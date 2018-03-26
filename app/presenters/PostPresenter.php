<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

class PostPresenter extends Nette\Application\UI\Presenter {

	/** @var Nette\Application\UI\Form	 */
	protected $form;

	/** @var Nette\Database\Context */
	private $database;
	protected $post = null;
	protected $nextPosts = [];

	const WHERE_NOT_FILLED_RECORD = " (coalesce(bird,'')='' OR body IS NULL OR head IS NULL) ";
	const WHERE_MORE_RECORD = " (parent_id IS NOT NULL OR isparent(id)) ";

	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
	}

	public function startup() {
		parent::startup();
		$this->template->moreFormVisible = false;
		$this->template->isMulti = false;
		$this->template->multiCount = 0;
		$this->template->multiTotal = 0;
	}

	public function actionYearMonthDone($year, $month, $offset) {
		$posts = $this->database
				->table('ptaci')
				->select("SQL_CALC_FOUND_ROWS *")
				->where("month(datetime)=?", $month)
				->where("year(datetime)=?", $year)
				->where("skipped=0")
				->where("deleted=0")
				->where(" NOT " . self::WHERE_NOT_FILLED_RECORD)
				->order("datetime,id")
				->limit(4, $offset);
		$this->post = $posts->fetch();
		while ($post = $posts->fetch()) {
			$this->nextPosts[] = $post;
		}
		$this->template->rowsTotal = $this->database->fetch("SELECT FOUND_ROWS() as pocet");
		$this->template->isMulti = $this->isMulti($this->post, $this->template->multiCount, $this->template->multiTotal);		
	}

	public function actionYearMonth($year, $month) {
		$posts = $this->database
				->table('ptaci')
				->where("month(datetime)=?", $month)
				->where("year(datetime)=?", $year)
				->where("skipped=0")
				->where("deleted=0")
				->where(self::WHERE_NOT_FILLED_RECORD)
				->where(" NOT " . self::WHERE_MORE_RECORD)
				->order("datetime,id")
				->limit(2);
		$this->post = $posts->fetch();
		$this->template->moreFormVisible = $this->isParentFormVisible($this->post);
		while ($post = $posts->fetch()) {
			$this->nextPosts[] = $post;
		}
	}

	public function actionYearMonthSkipped($year, $month, $offset) {
		$posts = $this->database
				->table('ptaci')
				->select("SQL_CALC_FOUND_ROWS *")
				->where("month(datetime)=?", $month)
				->where("year(datetime)=?", $year)
				->where("skipped=1")
				->where("deleted=0")
				->where(" NOT " . self::WHERE_MORE_RECORD)
				->order("datetime,id")
				->limit(2, $offset);
		$this->post = $posts->fetch();
		while ($post = $posts->fetch()) {
			$this->nextPosts[] = $post;
		}
		$this->template->rowsTotal = $this->database->fetch("SELECT FOUND_ROWS() as pocet");
		$this->template->moreFormVisible = $this->isParentFormVisible($this->post);
	}

	public function actionYearMonthMulti($year, $month, $offset) {
		$posts = $this->database
				->table('ptaci')
				->select("SQL_CALC_FOUND_ROWS *")
				->where("month(datetime)=?", $month)
				->where("year(datetime)=?", $year)
				->where("deleted=0")
				->where(self::WHERE_NOT_FILLED_RECORD)
				->where(self::WHERE_MORE_RECORD)
				->order("datetime,id")
				->limit(2, $offset);
		$this->post = $posts->fetch();
		$this->template->rowsTotal = $this->database->fetch("SELECT FOUND_ROWS() as pocet");
		$this->template->moreFormVisible = $this->isParentFormVisible($this->post);
		$this->template->isMulti = $this->isMulti($this->post, $this->template->multiCount, $this->template->multiTotal);
		while ($post = $posts->fetch()) {
			$this->nextPosts[] = $post;
		}
	}

	public function renderYearMonthDone($year, $month, $offset) {
		$this->template->post = $this->post;
		$this->template->year = $year;
		$this->template->month = $month;
		$this->template->offset = $offset;
		$this->template->nextPosts = $this->nextPosts;
	}

	public function renderYearMonth() {
		$this->template->post = $this->post;
		$this->template->nextPosts = $this->nextPosts;
	}

	public function renderYearMonthSkipped($year, $month, $offset) {
		$this->template->post = $this->post;
		$this->template->year = $year;
		$this->template->month = $month;

		$this->template->nextPosts = $this->nextPosts;
		$this->template->offset = $offset;
	}

	public function renderYearMonthMulti($year, $month, $offset) {
		$this->template->post = $this->post;
		$this->template->year = $year;
		$this->template->month = $month;

		$this->template->nextPosts = $this->nextPosts;
		$this->template->offset = $offset;
	}

	protected function createComponentSkipForm() {
		$skipForm = new Form;
		$skipForm->addHidden("id")
				->setDefaultValue($this->post->id);
		$skipForm->addSubmit('send', 'Přeskočit');
		$skipForm->onSuccess[] = [$this, 'skipFormSucceeded'];
		return $skipForm;
	}

	protected function createComponentDeleteForm() {
		$deleteForm = new Form;
		$deleteForm->addHidden("id")
				->setDefaultValue($this->post->id);
		$deleteForm->addSubmit('delete', 'Smazat')->setAttribute("onclick", "return confirm('Určitě chcete záznam smazat?')");
		$deleteForm->onSuccess[] = [$this, 'deleteFormSucceeded'];
		return $deleteForm;
	}

	protected function createComponentMoreForm() {
		$moreForm = new Form;
		$moreForm->addHidden("id")
				->setDefaultValue($this->post->id);
		$moreForm->addText('count', 'Počet ptáků')
				->addRule(Form::INTEGER, 'Počet ptáků musí být celé číslo')
				->addRule(Form::RANGE, 'Počet ptáků musí být od %d do %d', [2, 20])
				->setRequired("Je nutno zadat počet ptáků");
		$moreForm->addSubmit('send', 'Nastavit počet');
		$moreForm->onSuccess[] = [$this, 'moreFormSucceeded'];
		return $moreForm;
	}

	protected function createComponentBirdForm() {
		$this->form = new Form;
		if (!is_object($this->post)) {
			throw new Nette\Neon\Exception("Chybi post");
		}

		$this->form->addHidden("id")
				->setDefaultValue($this->post->id);

		$this->form->addText('bird', 'Druh ptáka')
				->setRequired("Druh musí být vyplněn");

		$this->form->addText('body', 'Orientace těla')
				->addRule(Form::INTEGER, 'Orientace musí být celé číslo')
				->addRule(Form::RANGE, 'Orientace musí být od %d do %d stupňů', [0, 359])
				->setRequired("Je nutno zadat orientaci těla");

		$this->form->addText('bodyx', 'body X');

		$this->form->addText('bodyy', 'body Y');


		$this->form->addText('head', 'Orientace hlavy')
				->addRule(Form::INTEGER, 'Orientace musí být celé číslo')
				->addRule(Form::RANGE, 'Orientace musí být od %d do %d stupňů', [0, 359])
				->setRequired("Zadej kam čumí");

		$this->form->addText('headx', 'head X');

		$this->form->addText('heady', 'head Y');

		$this->form->addSubmit('send', 'Uložit');

		$this->form->onSuccess[] = [$this, 'birdFormSucceeded'];

		$this->form->setDefaults([
			'head' => $this->post->head,
			'body' => $this->post->body,
			'bird' => $this->post->bird,
			'bodyx' => $this->post->bodyx,
			'bodyy' => $this->post->bodyy,
			'headx' => $this->post->headx,
			'heady' => $this->post->heady,
		]);

		return $this->form;
	}

	public function birdFormSucceeded($form, $values) {
		$postId = $values->id;

		$this->database->table('ptaci')->where('id = ?', $postId)->update([
			'head' => $values->head,
			'body' => $values->body,
			'bird' => $values->bird,
			'bodyx' => $values->bodyx,
			'bodyy' => $values->bodyy,
			'headx' => $values->headx,
			'heady' => $values->heady,
			'skipped' => 0,
			'ip' => $this->getHttpRequest()->getRemoteAddress(),
		]);

		//$this->flashMessage('Děkuji za komentář', 'success');
		$this->redirect('this');
	}

	public function skipFormSucceeded($form, $values) {
		$postId = $values->id;
		$this->database->table('ptaci')->where('id = ?', $postId)->update([
			'skipped' => 1,
			'ip' => $this->getHttpRequest()->getRemoteAddress(),
		]);
		//$this->flashMessage('Děkuji za komentář', 'success');
		$this->redirect('this');
	}

	public function moreFormSucceeded($form, $values) {
		$postId = $values->id;
		for ($i = 2; $i <= $values->count; $i++) {
			$this->template->posts = $this->database->query(""
					. "INSERT INTO ptaci "
					. "(parent_id,file,bird,datetime,body,head,place,ip,skipped,bodyx,bodyy,headx,heady,deleted,updated_time) "
					. "SELECT "
					. "id,file,bird,datetime,body,head,place,ip,skipped,bodyx,bodyy,headx,heady,deleted,updated_time "
					. "FROM ptaci "
					. "WHERE id = ?", $postId);
		}
		$this->redirect('this');
	}

	public function deleteFormSucceeded($form, $values) {
		$postId = $values->id;
		$this->database->table('ptaci')->where('id = ?', $postId)->update([
			'deleted' => 1,
			'ip' => $this->getHttpRequest()->getRemoteAddress(),
		]);
		$this->redirect('this');
	}

	protected function isParentFormVisible($post) {
		if (!isset($post['id'])) {
			return false;
		}
		if (isset($post['parent_id']) && $post['parent_id'] > 0) {
			return false;
		}
		$childsCount = $this->database->fetch("SELECT count(*) as poc FROM PTACI WHERE parent_id = ?", $post['id'])->poc;
		if ($childsCount > 0) {
			return false;
		}
		return true;
	}

	protected function isMulti($post, &$record, &$total) {
		if (!isset($post['id'])) {
			return false;
		}
		$childs = $this->database->query("SELECT *,count(*) as poc FROM ptaci WHERE parent_id = ? ORDER BY datetime,id", $post['id']);
		$child = $childs->fetch();
		if ($child->poc > 0) {
			//parent
			$record = 1;
			$total = $child->poc + 1;
			return true;
		} else if (isset($post['parent_id']) && $post['parent_id'] > 0) {
			$childs = $this->database->query("SELECT *,count(*) as poc FROM ptaci WHERE parent_id = ? ORDER BY datetime,id", $post['parent_id']);
			$child = $childs->fetch();
			$total = $child->poc + 1;
			//child
			$i = 2;
			if ($child->id == $post['id']) {
				$record = $i;
				return true;
			}
			while ($child = $childs->fetch()) {
				$i++;
				if ($child->id == $post['id']) {
					$record = $i;
					return true;
				}
			}
		}
		return false;
	}

}
