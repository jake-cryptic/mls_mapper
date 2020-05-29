<?php

class User {

	private $id;
	private $level;
	private $name;
	private $email;
	private $bookmarks;

	public function __construct($id, $level, $name, $email, $bookmarks){
		$this->id = $id;
		$this->level = $level;
		$this->name = $name;
		$this->email = $email;
		$this->bookmarks = $bookmarks;
	}

	public function getId() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	public function getBookmarks() {
		return $this->bookmarks;
	}

}