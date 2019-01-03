<?php
namespace hisi;

class Xml {

	var $parser;
	var $document;
	var $stack;
	var $data;
	var $last_opened_tag;
	var $isnormal;
	var $attrs = array();
	var $failed = FALSE;

	function __construct($isnormal) {
		$this->XMLparse($isnormal);
	}

	function XMLparse($isnormal) {
		$this->isnormal = $isnormal;
		$this->parser = xml_parser_create('ISO-8859-1');
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'open','close');
		xml_set_character_data_handler($this->parser, 'data');
	}

	function destruct() {
		xml_parser_free($this->parser);
	}

	function parse(&$data) {
		$this->document = array();
		$this->stack	= array();
		return xml_parse($this->parser, $data, true) && !$this->failed ? $this->document : '';
	}

	function open(&$parser, $tag, $attributes) {
		$this->data = '';
		$this->failed = FALSE;
		if(!$this->isnormal) {
			if(isset($attributes['id'])) {
				$this->document  = &$this->document[$attributes['id']];
			} else {
				$this->failed = TRUE;
			}
		} else {
			if(!isset($this->document[$tag]) || !is_string($this->document[$tag])) {
				$this->document  = &$this->document[$tag];
			} else {
				$this->failed = TRUE;
			}
		}
		$this->stack[] = &$this->document;
		$this->last_opened_tag = $tag;
		$this->attrs = $attributes;
	}

	function data(&$parser, $data) {
		if($this->last_opened_tag != NULL) {
			$this->data .= $data;
		}
	}

	function close(&$parser, $tag) {
		if($this->last_opened_tag == $tag) {
			$this->document = $this->data;
			$this->last_opened_tag = NULL;
		}
		array_pop($this->stack);
		if($this->stack) {
			$this->document = &$this->stack[count($this->stack)-1];
		}
	}

}
