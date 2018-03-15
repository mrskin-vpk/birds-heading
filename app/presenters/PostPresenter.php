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

	const WHERE_NOT_FILLED_RECORD = " (coalesce(bird,'')='' OR coalesce(body,0)=0 OR coalesce(head,0)=0) ";

	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
	}

	public function actionShow($postId) {
		$this->post = $this->database->table('ptaci')->get($postId);
	}

	public function actionYearMonth($year, $month) {
		$this->post = $this->database
				->table('ptaci')
				->where("month(datetime)=?", $month)
				->where("year(datetime)=?", $year)
				->where("skipped=0")
				->where(self::WHERE_NOT_FILLED_RECORD)
				->order("datetime")
				->limit(1)
				->fetch();
	}

	public function actionYearMonthSkipped($year, $month) {
		$this->post = $this->database
				->table('ptaci')
				->where("month(datetime)=?", $month)
				->where("year(datetime)=?", $year)
				->where("skipped=1")
				->where(self::WHERE_NOT_FILLED_RECORD)
				->order("datetime")
				->limit(1)
				->fetch();
	}

	public function renderShow($postId) {
		$this->template->post = $this->post;
	}

	public function renderYearMonth($year, $month) {
		$this->template->post = $this->post;
	}

	public function renderYearMonthSkipped($year, $month) {
		$this->template->post = $this->post;
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
			'headx' => $values->head,
			'heady' => $values->head,
		]);

		$this->flashMessage('Děkuji za komentář', 'success');
		$this->redirect('this');
	}

}
