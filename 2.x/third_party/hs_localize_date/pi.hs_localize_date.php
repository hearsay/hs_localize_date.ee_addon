<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
=========================================================================
Copyright (c) 2010 Kevin Smith <kevin@gohearsay.com>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
=========================================================================
 File: pi.hs_localize_date.php V1.0.0
-------------------------------------------------------------------------
 Purpose: Localize dates/times to a user's preference.
=========================================================================
CHANGE LOG :

October 22, 2010
	- Version 1.1.0
	- New feature: set global defaults in config.php file.

October 20, 2010
	- Version 1.0.0
	- First release
=========================================================================
*/

$plugin_info = array(
						'pi_name'			=> 'HS Localize Date',
						'pi_version'		=> '1.1.0',
						'pi_author'			=> 'Kevin Smith',
						'pi_author_url'		=> 'http://www.gohearsay.com/',
						'pi_description'	=> 'Displays dates and times localized based on user preference.',
						'pi_usage'			=> Hs_localize_date::usage()
					);

class Hs_localize_date
{

    var $return_data = '';

	function Hs_localize_date() 
	{
		$this->EE =& get_instance();
		
		$output = '';
		$us_date = $this->EE->config->item('hsld_us_date') ? str_replace('%', '', $this->EE->config->item('hsld_us_date')) : 'F d, Y';
		$us_time = $this->EE->config->item('hsld_us_time') ? str_replace('%', '', $this->EE->config->item('hsld_us_time')) : 'h:ia';
		$eu_date = $this->EE->config->item('hsld_eu_date') ? str_replace('%', '', $this->EE->config->item('hsld_eu_date')) : 'd F Y';
		$eu_time = $this->EE->config->item('hsld_eu_time') ? str_replace('%', '', $this->EE->config->item('hsld_eu_time')) : 'G:i';
		$separator = $this->EE->config->item('hsld_separator') ? str_replace('%', '', $this->EE->config->item('hsld_separator')) : '@';

		// Fetch the tagdata
		if (is_numeric($this->EE->TMPL->tagdata))
		{
			$timestamp = $this->EE->TMPL->tagdata;
		}
		else
		{
			return;
		}
			
		// Is there a custom format param?
		$custom = str_replace('%', '', $this->EE->TMPL->fetch_param('custom'));
		
		// Custom formats
		$us_custom = str_replace('%', '', $this->EE->TMPL->fetch_param('us'));
		$eu_custom = str_replace('%', '', $this->EE->TMPL->fetch_param('eu'));
		
		// Append the time?
		if ($this->EE->TMPL->fetch_param('time'))
		{
			$include_time = strtolower($this->EE->TMPL->fetch_param('time'));
		}
		else
		{
			$include_time = $this->EE->config->item('hsld_include_time') ? $this->EE->config->item('hsld_include_time') : FALSE;
		}

		// What should we use to separate the date and time?
		$separator = $this->EE->TMPL->fetch_param('separator') ? $this->EE->TMPL->fetch_param('separator') : $separator;
		
		// Format priority = Custom, member default pref, site default pref.
		if ($custom)
		{
			$time_format = 'custom';
			$php_time = $custom;
			
			$output .= date($php_time, $timestamp);
			$this->return_data = $output;
			return;
		}
		else
		{
			$time_format = $this->EE->session->userdata('time_format') ? $this->EE->session->userdata('time_format') : $this->EE->config->item('time_format');
			
			if ($time_format == 'us')
			{
				if ($us_custom)
				{
					$php_time = $us_custom;
				}
				else
				{
					if ($include_time == 'include')
					{
						$output .= date($us_date, $timestamp) ." ". $separator ." ". date($us_time, $timestamp);
						$this->return_data = $output;
						return;
					}
					elseif ($include_time == 'only')
					{
						$php_time = $us_time;
					}
					else
					{
						$php_time = $us_date;
					}
				}
			}
			elseif ($time_format == 'eu')
			{
				if ($eu_custom)
				{
					$php_time = $eu_custom;
				}
				else
				{
					if ($include_time == 'include')
					{
						$output .= date($eu_date, $timestamp) ." ". $separator ." ". date($eu_time, $timestamp);
						$this->return_data = $output;
						return;
					}
					elseif ($include_time == 'only')
					{
						$php_time = $eu_time;
					}
					else
					{
						$php_time = $eu_date;
					}
				}
			}
			
		}
		
		$php_time = str_replace('%', '', $php_time);
		
		$output .= date($php_time, $timestamp);
		$this->return_data = $output;
		return;
		
	}
	
// END


// ----------------------------------------
//  Plugin Usage
// ----------------------------------------
// This function describes how the plugin is used.
// Make sure and use output buffering

function usage()
{
ob_start(); 
?>

HS Localize Date allows you to display dates and times localized based on the member's preference for Time Formatting. (Members can set this preference in their Control Panel under Personal Settings > Localization.)

It's quite simple to use. Just wrap the plugin's tags around any tag that outputs UNIX timestamp (such as {current_time}) or around a raw UNIX timestamp itself.

=====================================================
Example
=====================================================

{exp:hs_localize_date}{current_time}{/exp:hs_localize_date}

If the date right now is October 19, 2010, then this would output the following for a user with their Time Formatting preference set just so.

United States:	October 19, 2010
European Union:	19 October 2010

=====================================================
Parameters
=====================================================

time=""
- Set to "include" to tack on the time after the date. This is locale-specific.
- Set to "only" to output the time by itself. This is locale-specific.

separator=""
- By default, the date and time are separated by '@'. Use this parameter to change it to whatever you like.

us="" (e.g. us="%D, %F %d, %Y - %g:%i:%s")*
- Provide custom date formatting to apply when the Time Formatting preference is set to United States. This overrides the default US date formatting.

eu="" (e.g. eu="%D, %F %d, %Y - %g:%i:%s")*
- Provide custom date formatting to apply when the Time Formatting preference is set to European Union. This overrides the default EU date formatting.

custom="" (e.g. custom="%D, %F %d, %Y - %g:%i:%s")*
- Provide custom date formatting that will override all other parameters and config.php settings.

* For parameters using date formatting codes, full documentation is found in the "Date Variable Formatting" page in the ExpressionEngine docs. The plugin ignores the 'time' and 'separator' parameters if you use either 'us', 'eu', or 'custom'.

=====================================================
Global Settings
=====================================================

I wanted this to remain a simple plugin, so to keep it from growing into a module, you can create global settings that will override the defaults of the plugin.

So for example, the default for US dates in the plugin produces 'October 19, 2010', but suppose you want to have a different look on your site. Easy: just add a few lines to your config.php file.

These are the config items, all of them optional, with examples.

$config['hsld_us_date'] = '%n/%j/%y';
$config['hsld_us_time'] = '%g:%i%a';
$config['hsld_eu_date'] = '%n/%j/%y';
$config['hsld_eu_time'] = '%H:%i';
$config['hsld_include_time'] = 'include'; (Identical in function to 'time' parameter.)
$config['hsld_separator'] = 'at'; (Identical in function to 'separator' parameter.)

* Just like with the parameters, you'll notice that the date formatting conforms to the "Date Variable Formatting" page in the ExpressionEngine docs.

Note: Global settings override the plugin's default settings, and plugin tag parameters override both global settings and the plugin's default settings.

<?php

$buffer = ob_get_contents();

ob_end_clean(); 

return $buffer;
}
// END
}
// END CLASS
?>
