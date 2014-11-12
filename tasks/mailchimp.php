<?php

namespace Fuel\Tasks;

class MailChimp
{

	public static function run()
	{
		\Cli::write();
		\Cli::write('Available tasks:');
		\Cli::write();
		\Cli::write('* lists_list: '.\Cli::color('Retrieve all of the lists defined for your user account', 'dark_gray'));
		\Cli::write('* lists_members: ([name])'.\Cli::color('Get all of the list members for a list', 'dark_gray'));
		\Cli::write('* lists_batch_subscribe: ([name])'.\Cli::color('Subscribe a batch of email addresses to a list at once', 'dark_gray'));
		\Cli::write('* lists_batch_unsubscribe: ([name])'.\Cli::color('Unsubscribe a batch of email addresses from a list', 'dark_gray'));
		\Cli::write('* lists_subscribe: [email] ([name])'.\Cli::color('Subscribe the provided email to a list', 'dark_gray'));
		\Cli::write('* lists_unsubscribe: [email] ([name])'.\Cli::color('Unsubscribe the given email address from the list', 'dark_gray'));
		\Cli::write();
	}

	public static function lists_list()
	{
		$mail_chimp = \FuelMailChimp\MailChimp::forge();

		$result = $mail_chimp->lists()
			->get_list();

		static::_display_result($result);
	}

	public static function lists_members($name = null)
	{
		$mail_chimp = \FuelMailChimp\MailChimp::forge();

		$result = $mail_chimp->lists($name)
			->get_members();

		static::_display_result($result);
	}

	public static function lists_subscribe($email, $name = null)
	{
		\Config::load('mailchimp', true);
		$list_config = \Config::get('mailchimp.lists.'.$name, \Config::get('mailchimp.lists.default'));

		$merge_vars = array();

		if(! empty($list_config['merge_vars']))
		{
			foreach ($list_config['merge_vars'] as $field => $options)
			{
				$value = \Cli::option($field, '');

				if (isset($options['allowed_values']) and ! in_array($value, $options['allowed_values']))
				{
					if (isset($options['allowed_values'][$value]))
					{
						$value = $options['allowed_values'][$value];
					}
					else
					{
						\Cli::write($field.' must be one of this value '.implode(', ', $options['allowed_values']));
						exit;
					}
				}
				$merge_vars[$options['var']] = $value;
			}
		}

		$mail_chimp = \FuelMailChimp\MailChimp::forge();

		$result = $mail_chimp->lists($name)
			->subscribe($email, $merge_vars);

		static::_display_result($result);
	}

	public static function lists_unsubscribe($email, $name = null)
	{

		$mail_chimp = \FuelMailChimp\MailChimp::forge();

		$result = $mail_chimp->lists($name)
			->unsubscribe($email);

		static::_display_result($result);
	}

	public static function lists_batch_subscribe($name = null)
	{
		\Config::load('mailchimp', true);
		$api_key = \Config::get('mailchimp.api_key');
		$list_config = \Config::get('mailchimp.lists.'.$name, \Config::get('mailchimp.lists.default'));

		$users = \Model\User::query()->get();

		$batch = array();

		foreach ($users as $user)
		{
			if (!empty($user->verified_email) and ( !empty($user->business_development) or ! empty($user->business_partners)))
			{
				$batch[$user->id] = array(
					'email' => array(
						'email' => $user->email,
					),
				);
				foreach ($list_config['merge_vars'] as $field => $options)
				{
					if (!empty($user->$field))
					{
						if (isset($options['allowed_values']) and ! in_array($user->$field, $options['allowed_values']))
						{
							if (isset($options['allowed_values'][$user->$field]))
							{
								$user->$field = $options['allowed_values'][$user->$field];
							}
							else
							{
								\Cli::write($field.' must be one of this value '.implode(', ', $options['allowed_values']));
								exit;
							}
						}
						$batch[$user->id]['merge_vars'][$options['var']] = $user->$field;
					}
				}
			}
		}

		$mail_chimp = \FuelMailChimp\MailChimp::forge();

		$result = $mail_chimp->lists($name)
			->batch_subscribe($batch);

		static::_display_result($result);
	}

	public static function lists_batch_unsubscribe($name = null)
	{
		$users = \Model\User::query()->get();

		$batch = array();

		foreach ($users as $user)
		{
			if (!empty($user->verified_email))
			{
				$batch[$user->id] = array(
					'email' => array(
						'email' => $user->email,
					),
				);
			}
		}

		$mail_chimp = \FuelMailChimp\MailChimp::forge();

		$result = $mail_chimp->lists($name)
			->batch_unsubscribe($batch);

		static::_display_result($result);
	}

	protected static function _display_result($result)
	{
		if (!empty($result['errors']))
		{
			static::_display_error($result);
			exit;
		}
		\Cli::write(\Format::forge($result)->to_yaml());
	}

	protected static function _display_error($result)
	{
		if (!empty($result['errors']))
		{
			foreach ($result['errors'] as $error)
			{
				\Cli::write(\Format::forge($error)->to_yaml());
			}
		}
	}

}
