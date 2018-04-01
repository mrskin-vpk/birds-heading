<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Presenters;

use Nette;

/**
 * Description of ExportPresenter
 *
 * @author petr
 */
class ExportPresenter extends Nette\Application\UI\Presenter {

	const ORIG_PATH = "f:\\ptaci\\all";
	const EXPORT_PATH = "f:\\ptaci\\exp";

	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database) {
		set_time_limit(3600);
		$this->database = $database;
	}

	public function actionCopyProgress() {
		$this->template->exportDir = self::EXPORT_PATH;
		//pouze zobrazuje progress
	}

	public function actionData() {
		$this->template->posts = $this->getPostsExportData();
	}

	public function actionCopy() {
		$this->deletePrevious();
		$this->copyImages();
		//$this->mycopy(self::ORIG_PATH . "1-20170731-1545-1200.JPG", self::EXPORT_PATH . 'f:\ptaci\exp\1\1-20170731-1545-1201.JPG');
	}

	public function echoProgress($string) {
		echo $string;
		ob_flush();
		flush();
	}

	public function copyImages() {
		$posts = $this->getPostsExportData();
		$i = 0;
		$total = count($posts);
		echo "--" . $total . "--";
		$onePerc = 100 / $total;
		$prevPerc = 0;
		foreach ($posts as $post) {
			$i++;
			$perc = floor($onePerc * $i);
			if ($perc != $prevPerc) {
				$this->echoProgress("copy progress " . $perc . "%");
				$prevPerc = $perc;
			}
			$orig = $this->getOrigFullPath($post->file);
			$exp = $this->getExportFullPath($post->file, $post->year, $post->month, $post->place);
			$this->mycopy($orig, $exp);
		}
	}

	private function getPostsExportData() {
		return $this->database
						->table('ptaci')
						->select("SQL_CALC_FOUND_ROWS *,month(datetime) month, year(datetime) year")
						->where("deleted=0")
						->where(" NOT " . PostPresenter::WHERE_NOT_FILLED_RECORD)
						->order("datetime")
						->fetchAll();
	}

	private function getOrigFullPath($fileName) {
		return self::ORIG_PATH . "\\" . $fileName;
	}

	private function getExportFullPath($fileName, $year, $month, $locality) {
		return self::EXPORT_PATH . "\\" . $locality . "\\" . $year . "-" . str_pad($month, 2, "0", STR_PAD_LEFT) . "\\" . $fileName;
	}

	private function mycopy($s1, $s2) {
		//$this->echoProgress("copy file " . $s1 . " -> " . $s2);
		$path = pathinfo($s2);
		if (!file_exists($path['dirname'])) {
			mkdir($path['dirname'], 0777, true);
		}
		if (!copy($s1, $s2)) {
			throw new \Nette\Neon\Exception("copy " . $s1 . " to " . $s2 . " failed \n");
		}
	}

	private function deletePrevious() {
		$this->echoProgress("deleting prev export");
		$this->deleteAllInFolder(self::EXPORT_PATH);
	}

	private function deleteAllInFolder($dirName) {
		$files = glob($dirName . '\\*');
		foreach ($files as $file) { // iterate files
			if (is_dir($file)) {
				$this->deleteAllInFolder($file);
				//$this->echoProgress("deleting dir " . $file);
				rmdir($file); // delete empty folder
			} elseif (is_file($file)) {
				//$this->echoProgress("deleting file " . $file);
				unlink($file); // delete file
			}
		}
	}

}
