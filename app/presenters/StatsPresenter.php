<?php

namespace App\Presenters;

use Nette;

class StatsPresenter extends Nette\Application\UI\Presenter {

	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
	}

	public function renderUsers() {
		$this->template->posts = $this->database->query(""
				. "SELECT "
				. "ptaci.ip ip,ip.name name,count(*) as count "
				. "FROM `ptaci` "
				. "LEFT JOIN ip on ptaci.ip=ip.ip "
				. "WHERE NOT(" . PostPresenter::WHERE_NOT_FILLED_RECORD . ") "
				. "GROUP BY ptaci.ip,ip.name "
		);
	}

}
