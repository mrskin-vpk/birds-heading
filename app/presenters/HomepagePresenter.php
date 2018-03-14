<?php

namespace App\Presenters;

use Nette;

class HomepagePresenter extends Nette\Application\UI\Presenter {

	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
	}

//
//	public function renderDefault() {
//		$this->template->posts = $this->database->table->count('ptaci')
//				->order('datetime DESC')
//				->limit(5);
//	}
//	
	public function renderDefault() {
		$this->template->posts = $this->database->query(""
		. "SELECT "
		. "count(*) total,COUNT(IF(head>0 and skipped = 0,1,NULL)) filled,COUNT(IF(head>0 and skipped = 1,1,NULL)) skipped,place,year(datetime) year,month(datetime) month "
		. "FROM `ptaci` group by place,year(datetime),month(datetime) "
		. "ORDER BY datetime");
	}	

}
