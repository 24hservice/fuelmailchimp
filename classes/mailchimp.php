<?php

namespace FuelMailChimp;

class MailChimp
{
	protected static $_instances;
	protected $_instance;
	protected $_driver;
	protected $_api_key;

	public static function forge($instance = 'default')
	{
		if(! isset(static::$_instances[$instance]))
		{
			static::$_instances[$instance] = new static($instance);
		}
		return static::$_instances[$instance];
	}
	
	public function __construct($instance = 'default')
	{
		$this->_instance = $instance;
		\Config::load('mailchimp', true);
		$this->_api_key = \Config::get('mailchimp.api_key', null);
		$this->_api_key and $this->_driver = new \Drewm\MailChimp($this->_api_key);
	}

	public function lists($name = 'default')
	{
		return new \FuelMailChimp\Lists($this, $name);
	}

	public function call($method, $args = array(), $timeout = 10)
	{
		if(! empty($this->_driver))
		{
			return $this->_driver->call($method, $args, $timeout);
		}
	}
}
