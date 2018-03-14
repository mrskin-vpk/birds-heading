<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

class PostPresenter extends Nette\Application\UI\Presenter {

	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
	}

	public function renderShow($postId) {
		$this->template->post = $this->database->table('ptaci')->get($postId);
	}

	public function renderYearMonth($year, $month) {
		$this->template->post = $this->database
				->table('ptaci')
				->where("month(datetime)=?", $month)
				->where("year(datetime)=?", $year)
				->where("skipped=0")
				->order("datetime")
				->limit(1)
				->fetch();
		\Tracy\Debugger::barDump($this->template->post, 'post');
		\Tracy\Debugger::barDump($year, 'yeaar');
		\Tracy\Debugger::barDump($month, 'month');
	}
	
	public function renderYearMonthSkipped($year, $month) {
		$this->template->post = $this->database
				->table('ptaci')
				->where("month(datetime)=?", $month)
				->where("year(datetime)=?", $year)
				->where("skipped=1")
				->order("datetime")
				->limit(1)
				->fetch();
		\Tracy\Debugger::barDump($this->template->post, 'post');
		\Tracy\Debugger::barDump($year, 'yeaar');
		\Tracy\Debugger::barDump($month, 'month');
	}
	

	protected function createComponentBirdForm() {
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('bird', 'Druh ptáka')
				->setRequired();

		$form->addText('body', 'Orientace těla')
				->setRequired();

		$form->addText('head', 'Orientace hlavy')
				->setRequired();

		$form->addSubmit('send', 'Ulozit');

		$form->onSuccess[] = [$this, 'birdFormSucceeded'];

		return $form;
	}

	public function birdFormSucceeded($form, $values) {
		$postId = $this->getParameter('postId');

		$this->database->table('ptaci')->where('id = ?', $postId)->update([
			'head' => $values->head,
			'body' => $values->body,
			'bird' => $values->bird,
		]);

		$this->flashMessage('Děkuji za komentář', 'success');
		$this->redirect('this');
	}

}
