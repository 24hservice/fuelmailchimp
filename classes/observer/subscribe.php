<?php

namespace FuelMailChimp;

class Observer_Subscribe extends \Orm\Observer
{
	public static $list_name = 'default';
	public static $email_field = 'email';
	public static $subscribe_field = 'newsletter';

	protected $_list_name;
	protected $_email_field;
	protected $_subscribe_field;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_list_name = isset($props['list_name']) ? $props['list_name'] : static::$list_name;
		$this->_email_field = isset($props['email_field']) ? $props['email_field'] : static::$email_field;
		$this->_subscribe_field = isset($props['subscribe_field']) ? $props['subscribe_field'] : static::$subscribe_field;
	}

	public function after_insert(\Orm\Model $model)
	{
		$this->after_save($model);
	}

	public function after_update(\Orm\Model $model)
	{
		$this->after_save($model);
	}

	public function after_save(\Orm\Model $model)
	{
		$mail_chimp = \FuelMailChimp\MailChimp::forge();

		$list = $mail_chimp->lists($this->_list_name);

		$want_subscribe_to_newsletter = false;

		if (method_exists($model, 'want_subscribe_to_newsletter'))
		{
			$want_subscribe_to_newsletter = $model->want_subscribe_to_newsletter();
		}
		else
		{
			$want_subscribe_to_newsletter = $model->{$this->_subscribe_field};
		}

		if($want_subscribe_to_newsletter)
		{
			\Config::load('mailchimp', true);
			$list_config = \Config::get('mailchimp.lists.'.$this->_list_name);

			$merge_vars = array();

			foreach ($list_config['merge_vars'] as $field => $options)
			{
				$value = isset($model->$field) ? $model->$field : null;

				if (isset($options['allowed_values']) and ! in_array($value, $options['allowed_values']))
				{
					if (isset($options['allowed_values'][$value]))
					{
						$value = $options['allowed_values'][$value];
					}
					else
					{
						throw new \Exception($field.' must be one of this value '.implode(', ', $options['allowed_values']));
					}
				}
				$merge_vars[$options['var']] = $value;
			}

			$list->subscribe($model->{$this->_email_field}, $merge_vars);
		}
		else
		{
			$list->unsubscribe($model->{$this->_email_field});
		}
	}

}
