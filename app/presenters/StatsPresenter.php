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

		$this->template->edits = $this->database->query(""
				. "SELECT "
				. "ptaci.ip ip,ip.name name,file,updated_time,datetime,place "
				. "FROM `ptaci` "
				. "LEFT JOIN ip on ptaci.ip=ip.ip "
				. "WHERE NOT(" . PostPresenter::WHERE_NOT_FILLED_RECORD . ") "
				. "ORDER BY updated_time DESC "
				. "LIMIT 50 "
		);
		$recs = $this->template->edits->fetchAll();
		$this->template->diff = [];
		$last = [];
		for ($i = count($recs); $i > 0; $i--) {
			$this->template->diff[$i] = $recs[$i-1]->datetime;
			if (isset($last[$recs[$i-1]->ip])) {
				$this->template->diff[$i] = $last[$recs[$i-1]->ip]->diff($recs[$i-1]->updated_time);
			} else {
				$this->template->diff[$i] = new \DateInterval("PT0S");
			}
			$last[$recs[$i-1]->ip] = $recs[$i-1]->updated_time;
		}
	}

}
