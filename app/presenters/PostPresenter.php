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

	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
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
				->order("datetime")
				->limit(4, $offset);
		$this->post = $posts->fetch();
		while ($post = $posts->fetch()) {
			$this->nextPosts[] = $post;
		}
		

		$this->template->rowsTotal = $this->database->fetch("SELECT FOUND_ROWS() as pocet");
	}

	public function actionYearMonth($year, $month) {
		$posts = $this->database
				->table('ptaci')
				->where("month(datetime)=?", $month)
				->where("year(datetime)=?", $year)
				->where("skipped=0")
				->where("deleted=0")
				->where(self::WHERE_NOT_FILLED_RECORD)
				->order("datetime")
				->limit(2);
		$this->post = $posts->fetch();
		while ($post = $posts->fetch()) {
			$this->nextPosts[] = $post;
		}
	}

	public function actionYearMonthSkipped($year, $month) {
		$posts = $this->database
				->table('ptaci')
				->where("month(datetime)=?", $month)
				->where("year(datetime)=?", $year)
				->where("skipped=1")
				->where("deleted=0")
				->where(self::WHERE_NOT_FILLED_RECORD)
				->order("datetime")
				->limit(2);
		$this->post = $posts->fetch();
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

	public function renderYearMonthSkipped() {
		$this->template->post = $this->post;
		$this->template->nextPosts = $this->nextPosts;
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
				->setRequired("Je notno zadat orientaci těla");

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
		]);

		//$this->flashMessage('Děkuji za komentář', 'success');
		$this->redirect('this');
	}

	public function skipFormSucceeded($form, $values) {
		$postId = $values->id;
		$this->database->table('ptaci')->where('id = ?', $postId)->update([
			'skipped' => 1,
		]);
		//$this->flashMessage('Děkuji za komentář', 'success');
		$this->redirect('this');
	}

	public function deleteFormSucceeded($form, $values) {
		$postId = $values->id;
		$this->database->table('ptaci')->where('id = ?', $postId)->update([
			'deleted' => 1,
		]);
		$this->redirect('this');
	}

}
