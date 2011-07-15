<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ARML Controller
 * Generates KML with PlaceMarkers and Category Styles
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module	   ARML Controller	
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
* 
*/

class Arml_Controller extends Controller
{
	// Table Prefix
	protected $table_prefix;

	public function __construct()
	{
		parent::__construct();

		// Set Table Prefix
		$this->table_prefix = Kohana::config('database.default.table_prefix');

		// Cacheable ARML Controller
		$this->is_cachable = TRUE;
		
		//$profile = new Profiler;
	}
	
	public function index()
	{
		$db = new Database();
		
		$latitude = (isset($_GET['latitude']) AND ! empty($_GET['latitude'])) ? 
			(float) $_GET['latitude'] : false;
		
		$longitude = (isset($_GET['longitude']) AND ! empty($_GET['longitude'])) ? 
			(float) $_GET['longitude'] : false;
			
		$max = (isset($_GET['maxNumberOfP']) AND ! empty($_GET['maxNumberOfP'])) ? 
			(int) $_GET['maxNumberOfP'] : 50;
		
		// We want to make sure the max number of reports that can be pulled is 500
		// That's pretty high too!
		$max = ($max > 500) ? 500 : $max;
		
		$reports = array();
		if ($latitude AND $longitude)
		{
			// Get Neighboring Markers Within 30 Kms (20 Miles)
			$reports = $db->query("SELECT DISTINCT i.*, l.`latitude`, l.`longitude`,
			((ACOS(SIN($latitude * PI() / 180) * SIN(l.`latitude` * PI() / 180) + COS($latitude * PI() / 180) * COS(l.`latitude` * PI() / 180) * COS(($longitude - l.`longitude`) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS distance
			 FROM `".$this->table_prefix."incident` AS i INNER JOIN `".$this->table_prefix."location` AS l ON (l.`id` = i.`location_id`) INNER JOIN `".$this->table_prefix."incident_category` AS ic ON (i.`id` = ic.`incident_id`) INNER JOIN `".$this->table_prefix."category` AS c ON (ic.`category_id` = c.`id`) WHERE i.incident_active=1 
			HAVING distance<='30'
			 ORDER BY distance ASC LIMIT $max ");
		}
		
		$url = url::base();
		$url = preg_replace("/^https?:\/\/(.+)$/i","\\1", $url);
		$site_id = rtrim($url,"/");
		
		header("Content-type: text/xml; charset=utf-8");
		
		$view = new View("arml");
		$view->site_url = url::base();
		$view->site_id = $site_id;
		$view->site_name = utf8_encode(htmlspecialchars(Kohana::config('settings.site_name')));
		$view->site_tagline = utf8_encode(htmlspecialchars(Kohana::config('settings.site_tagline')));
		$view->site_logo = url::base()."plugins/arml/views/images/ushahidi_logo.png";
		$view->site_email = utf8_encode(htmlspecialchars(Kohana::config('settings.site_email')));
		$view->reports = $reports;
		$view->render(TRUE);
	}
}