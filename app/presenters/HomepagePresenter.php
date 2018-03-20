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
				. "count(*) total,"
				. "COUNT(IF( NOT(" . PostPresenter::WHERE_NOT_FILLED_RECORD . "),1,NULL)) filled,"
				. "COUNT(IF( " . PostPresenter::WHERE_NOT_FILLED_RECORD . ",1,NULL)) notFilled,"
				. "COUNT(IF(skipped = 1,1,NULL)) skipped,"
				. "place,year(datetime) year,month(datetime) month "
				. "FROM `ptaci` "
				. "WHERE deleted=0 "
				. "GROUP BY place,year(datetime),month(datetime) "
				. "ORDER BY datetime");
	}

}
