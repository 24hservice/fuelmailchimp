<?php

namespace FuelMailChimp;

class Lists
{
	protected $_driver;
	protected $_name;
	protected $_config;

	public function __construct($driver, $name = 'default')
	{
		$this->_driver = $driver;
		empty($name) and $name = 'default';
		$this->_name = $name;
		$this->_config = \Config::get('mailchimp.lists.'.$this->_name);
	}

	public function get_list()
	{
		return $this->_driver->call('lists/list');
	}

	public function get_members()
	{
		return $this->_driver->call('lists/members', array(
			'id' => $this->_config['id'],
		));
	}

	public function subscribe($email, $merge_vars)
	{
		return $this->_driver->call('lists/subscribe', array(
			'id' => $this->_config['id'],
			'email' => array('email' => $email),
			'merge_vars' => $merge_vars,
			'double_optin' => false,
			'update_existing' => true,
			'replace_interests' => false,
			'send_welcome' => false,
		));
	}

	public function batch_subscribe($batch)
	{
		return $this->_driver->call('lists/batch-subscribe', array(
			'id' => $this->_config['id'],
			'batch' => $batch,
			'double_optin' => false,
			'update_existing' => true,
			'replace_interests' => false,
			'send_welcome' => false,
		));
	}

	public function unsubscribe($email)
	{
		return $this->_driver->call('lists/unsubscribe', array(
			'id' => $this->_config['id'],
			'email' => array('email' => $email),
			'delete_member' => false,
			'send_goodbye' => true,
			'send_notify' => true,
		));
	}

	public function batch_unsubscribe($batch)
	{
		return $this->_driver->call('lists/batch-unsubscribe', array(
			'id' => $this->_config['id'],
			'batch' => $batch,
			'delete_member' => false,
			'send_goodbye' => true,
			'send_notify' => true,
		));
	}
}
