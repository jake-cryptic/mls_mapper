<?php

class User {

	private $id;
	private $name;
	private $email;
	private $bookmarks;

	public function __constructor($id, $name, $email, $bookmarks){
		$this->id = $id;
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