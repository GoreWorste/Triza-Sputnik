<?php
/**
 * @package    com_cwhire
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
*/

defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/models/joblisting.php';

// abstract class CwhireHelper
// {
// 	public static function myFunction()
// 	{
// 		$result = 'Something';
// 		return $result;
// 	}
//
// }

class JoblistingHelper
{
	public $result = '';
	protected $item;

	public function allfields() {
		$app = JFactory::getApplication();
		$params = $app->getParams();

		$fields = array();

		$fields['appliform_title'] = $params->get('appliform_title');
		$fields['appliform_fname'] = $params->get('appliform_fname');
		$fields['appliform_sname'] = $params->get('appliform_sname');
		$fields['appliform_street'] = $params->get('appliform_street');
		$fields['appliform_country'] = $params->get('appliform_country');
		$fields['appliform_plz'] = $params->get('appliform_plz');
		$fields['appliform_address'] = $params->get('appliform_address');
		$fields['appliform_phone'] = $params->get('appliform_phone');
		$fields['appliform_mail'] = $params->get('appliform_mail');
		$fields['appliform_attachments'] = $params->get('appliform_attachments');
		$fields['appliform_vita'] = $params->get('appliform_vita');
		$fields['appliform_testi'] = $params->get('appliform_testi');

		return $fields;
	}

	public function usedfields() {
		$allfields = $this->allfields();
		$fields = array();
		foreach ($allfields as $key => $value) {
			if ($value != 0) {
				array_push($fields, $key);
			}
		}
		return $fields;
	}

	public function mustfields() {
		$allfields = $this->allfields();
		$required_fields = array();
		foreach ($allfields as $key => $value) {
			if ($value == 2) {
				array_push($required_fields, $key);
			}
		}
		return $required_fields;
	}
	// error_log("Test error!", 0);
	// public function submitForm($entry, $request)
	// {
	// 	JLog::add(JText::_('submitForm: '.print_r($entry, false)), JLog::INFO, 'com_cwhire');
	// 	$vitalog = "";
	// 	$testilog = "";
	//
	// 	$this->item = $entry;
	// // 	$appliform_title = $request::getVar('appliform_title');
	// // 	$appliform_fname = $request::getVar('appliform_fname');
	// // 	$appliform_sname = $request::getVar('appliform_sname');
	// // 	$appliform_street = $request::getVar('appliform_street');
	// // 	$appliform_country = $request::getVar('appliform_country');
	// // 	$appliform_plz = $request::getVar('appliform_plz');
	// // 	$appliform_address = $request::getVar('appliform_address');
	// // 	$appliform_phone = $request::getVar('appliform_phone');
	// // 	$appliform_mail = $request::getVar('appliform_mail');
	// // //   $appliform_www = $request::getVar('appliform_www');
	// // 	$appliform_vita = $request::getVar('appliform_vita');
	// // 	$appliform_testi = $request::getVar('appliform_testi');
	// // 	$appliform_extlink = $request::getVar('appliform_extlink');
	// // 	$application_text = $request::getVar('application_text');
	// // 	$privacy_policy_accepted = $request::getVar('privacy_policy_accepted');
	//
	// 	$appliform_title = $request['appliform_title'];
	// 	$appliform_fname = $request['appliform_fname'];
	// 	$appliform_sname = $request['appliform_sname'];
	// 	$appliform_street = $request['appliform_street'];
	// 	$appliform_country = $request['appliform_country'];
	// 	$appliform_plz = $request['appliform_plz'];
	// 	$appliform_address = $request['appliform_address'];
	// 	$appliform_phone = $request['appliform_phone'];
	// 	$appliform_mail = $request['appliform_mail'];
	// //   $appliform_www = $request['appliform_www'];
	// 	$appliform_vita = $request['appliform_vita'];
	// 	$appliform_testi = $request['appliform_testi'];
	// 	$appliform_extlink = $request['appliform_extlink'];
	// 	$application_text = $request['application_text'];
	// 	$privacy_policy_accepted = $request['privacy_policy_accepted'];
	//
	// 	$vorgangsnummer = rand(1550000,1999999);
	//
	// 	$app = JFactory::getApplication();
	// 	$params = $app->getParams();
	//
	// 	$usecss = $params->get('usecss');
	// 	$doc =& JFactory::getDocument();
	//
	// 	if ($usecss == 1) {
	// 	  $doc->addStyleSheet(JURI::base(true) . '/components/com_cwhire/assets/css/jobokay.css', 'text/css' );
	//   }
	//
	// 	// $filetypes_string = $params->get('attachment-types');
	// 	// $filetypes = explode(",", str_replace(" ", "", $filetypes_string));
	// 	// $filetypes = $params->get('attachment-types');
	// 	// $filetypes_string = implode(",", $filetypes);
	// 	$model = JModelLegacy::getInstance('Joblisting', 'CwhireModel');
	//
	// 	$filetypes_ids = $params->get('attachment-types');
	// 	$filetypes = $model->getExtensions($filetypes_ids);
	// 	$allowed_types = $model->getMimetypes($filetypes_ids);
	// 	$filetypes_string = implode(", ", $filetypes_ids);
	//
	// 	$sender = $params->get('sender');
	// 	$sendername = $params->get('sendername');
	//
	// 	$defaultmail = $params->get('recipient');
	//
	// 	$recipient = $model->getRecipient($this->item->jobcategory)->recipient;
	// 	if ($recipient == '') {
	// 		$recipient = $defaultmail;
	// 	}
	//
	// 	$bodycode = "
	// 	<table>
	// 		<tr>
	// 			<td>".JText::_('COM_CWHIRE_MAIL_NAME').": </td>
	// 			<td>".$appliform_title." ".$appliform_fname." ".$appliform_sname."</td>
	// 		</tr>
	// 		<tr>
	// 			<td>".JText::_('COM_CWHIRE_MAIL_STREET').": </td>
	// 			<td>".$appliform_street."</td>
	// 		</tr>
	// 		<tr>
	// 			<td>".JText::_('COM_CWHIRE_MAIL_CITY').": </td>
	// 			<td>".$appliform_country." ".$appliform_plz." ".$appliform_address."</td>
	// 		</tr>
	// 		<tr>
	// 			<td>".JText::_('COM_CWHIRE_MAIL_PHONE').": </td>
	// 			<td>".$appliform_phone."</td>
	// 		</tr>
	// 		<tr>
	// 			<td>".JText::_('COM_CWHIRE_MAIL_MAIL').": </td>
	// 			<td>".$appliform_mail."</td>
	// 		</tr>
	// 		<tr>
	// 			<td>".JText::_('COM_CWHIRE_MAIL_HOMEPAGE').": </td>
	// 			<td>".$appliform_extlink."</td>
	// 		</tr>
	// 	</table>
	// 	<br>
	// 	<p>$application_text</p>";
	//
	//
	//
	// 	$xml_code = "<LCS-XML-Doc>
	// 	<Geschaeftsstelle Anz=\"N\">
	// 	<GeschSt Anz=\"1\">1</GeschSt>
	// 	</Geschaeftsstelle>
	// 	<Vorgangsnummer Anz=\"N\">
	// 	<Vorgang1 Anz=\"1\"></Vorgang1>
	// 	<Vorgang2 Anz=\"1\"></Vorgang2>
	// 	<Kostenstvorgang Anz=\"1\">1</Kostenstvorgang>
	// 	</Vorgangsnummer>
	// 	<Personal Anz=\"1\">
	// 	<PersonalNr Anz=\"1\">".$vorgangsnummer."</PersonalNr>
	// 	<Nachname Anz=\"1\">".$appliform_sname."</Nachname>
	// 	<Vorname Anz=\"1\">".$appliform_fname."</Vorname>
	// 	<Kostenstpersonal Anz=\"1\"></Kostenstpersonal>
	// 	<Titel Anz=\"1\">$appliform_title</Titel>
	// 	<Vorsatzwort Anz=\"1\"></Vorsatzwort>
	// 	<Namenzusatz Anz=\"1\"></Namenzusatz>
	// 	<Adresse Anz=\"1\">
	// 	<Wohnhaftbei Anz=\"1\"></Wohnhaftbei>
	// 	<Strasse Anz=\"1\">".$appliform_street."</Strasse>
	// 	<Nat Anz=\"1\">".$appliform_country."</Nat>
	// 	<PLZ Anz=\"1\">".$appliform_plz."</PLZ>
	// 	<Ort Anz=\"1\">".$appliform_address."</Ort>
	// 	<Telefon1 Anz=\"1\">".$appliform_phone."</Telefon1>
	// 	<Telefon2 Anz=\"1\"></Telefon2>
	// 	<Telefax Anz=\"1\"></Telefax>
	// 	<Email Anz=\"1\">".$appliform_mail."</Email>
	// 	<Homepage Anz=\"1\">".$appliform_extlink."</Homepage>
	// 	</Adresse>
	// 	<GebDatum Anz=\"1\"></GebDatum>
	// 	<GebOrt></GebOrt>
	// 	<Geschlecht Anz=\"1\">m</Geschlecht>
	// 	<FamilienSt Anz=\"1\">n</FamilienSt>
	// 	<Staatsangehoerigkeit Anz=\"1\">".$appliform_country."</Staatsangehoerigkeit>
	// 	<Bundesland Anz=\"1\"></Bundesland>
	// 	<Gebiet></Gebiet>
	// 	<KFZ Anz=\"N\"></KFZ>
	// 	<Fuehrerschein Anz=\"N\"></Fuehrerschein>
	// 	<Fuehrerschein Anz=\"N\"></Fuehrerschein>
	// 	<Fuehrerschein Anz=\"N\"></Fuehrerschein>
	// 	<Reisebereitschaft Anz=\"1\"></Reisebereitschaft>
	// 	<Umzugsbereitschaft Anz=\"1\"></Umzugsbereitschaft>
	// 	<Pendelbereitschaft Anz=\"1\"></Pendelbereitschaft>
	// 	<Info4 Anz=\"1\"></Info4>
	// 	<Info5 Anz=\"1\"></Info5>
	// 	<Info6 Anz=\"1\"></Info6>
	// 	<Freitext Anz=\"1\">".$application_text."</Freitext>
	// 	<WVDatum Anz=\"1\"></WVDatum>
	// 	<WVUhrzeit Anz=\"1\"></WVUhrzeit>
	// 	<Bewerbung Anz=\"N\">
	// 	<Bezugsquelle Anz=\"1\"></Bezugsquelle>
	// 	<Bewerbungsdatum Anz=\"1\"></Bewerbungsdatum>
	// 	<ErstDatum Anz=\"1\"></ErstDatum>
	// 	<VerfuegbarAbDatum Anz=\"1\"></VerfuegbarAbDatum>
	// 	<VerfuegbarAbText Anz=\"1\"></VerfuegbarAbText>
	// 	<Gehaltsvorstellung Anz=\"1\"></Gehaltsvorstellung>
	// 	</Bewerbung>
	// 	<Kenntnis Anz=\"N\">
	// 	<KenntnisBez Anz=\"1\"></KenntnisBez>
	// 	<KenntnisBew Anz=\"1\"></KenntnisBew>
	// 	</Kenntnis>
	// 	<Kenntnis Anz=\"N\">
	// 	<KenntnisBez Anz=\"1\"></KenntnisBez>
	// 	<KenntnisBew Anz=\"1\"></KenntnisBew>
	// 	</Kenntnis>
	// 	<Kenntnissontiges Anz=\"1\"></Kenntnissontiges>
	// 	<Fachbereich Anz=\"1\"></Fachbereich>
	// 	<Fachbereich Anz=\"1\"></Fachbereich>
	// 	<FachbereichSonstiges Anz=\"1\"></FachbereichSonstiges>
	// 	<Beruf Anz=\"N\"></Beruf>
	// 	<Beruf Anz=\"N\"></Beruf>
	// 	<Berufsonstiges Anz=\"N\"></Berufsonstiges>
	// 	<BerufWunsch Anz=\"N\"></BerufWunsch>
	// 	<BerufTaetKenntnis Anz=\"N\">
	// 	<BerufTaetKenntnisBez Anz=\"1\"></BerufTaetKenntnisBez>
	// 	<BerufTaetKenntnisBew Anz=\"1\"></BerufTaetKenntnisBew>
	// 	</BerufTaetKenntnis>
	// 	<BerufTaetKenntnis Anz=\"N\">
	// 	<BerufTaetKenntnisBez Anz=\"1\"></BerufTaetKenntnisBez>
	// 	<BerufTaetKenntnisBew Anz=\"1\"></BerufTaetKenntnisBew>
	// 	</BerufTaetKenntnis>
	// 	<BerufTaetKenntnissontiges Anz=\"1\"></BerufTaetKenntnissontiges>
	// 	<Wuensche Anz=\"1\"></Wuensche>
	// 	<Schule Anz=\"N\">
	// 	<Von Anz=\"1\"></Von>
	// 	<Bis Anz=\"1\"></Bis>
	// 	<Bezeichnung Anz=\"1\"></Bezeichnung>
	// 	<Abschluss Anz=\"1\"></Abschluss>
	// 	<Bemerkung Anz=\"1\"></Bemerkung>
	// 	</Schule>
	// 	<Ausbildung Anz=\"N\">
	// 	<Von Anz=\"1\"></Von>
	// 	<Bis Anz=\"1\"></Bis>
	// 	<Betrieb Anz=\"1\"></Betrieb>
	// 	<Abschluss Anz=\"1\"></Abschluss>
	// 	<Bemerkung Anz=\"1\"></Bemerkung>
	// 	</Ausbildung>
	// 	<Beschaeftigung Anz=\"N\">
	// 	<Von Anz=\"1\"></Von>
	// 	<Bis Anz=\"1\"></Bis>
	// 	<Betrieb Anz=\"1\"></Betrieb>
	// 	<BeschBeruf Anz=\"1\"></BeschBeruf>
	// 	<Bemerkung Anz=\"1\"></Bemerkung>
	// 	</Beschaeftigung>
	// 	<Bildung Anz=\"N\">
	// 	<Von Anz=\"1\"></Von>
	// 	<Bis Anz=\"1\"></Bis>
	// 	<Bezeichnung Anz=\"1\"></Bezeichnung>
	// 	<Zertifikat Anz=\"1\"></Zertifikat>
	// 	<Bemerkung Anz=\"1\"></Bemerkung>
	// 	</Bildung>
	// 	<LebenSonstiges Anz=\"1\">
	// 	<Vollzeit Anz=\"1\"></Vollzeit>
	// 	<Teilzeit Anz=\"1\"></Teilzeit>
	// 	</LebenSonstiges>
	// 	</Personal>
	// 	</LCS-XML-Doc>\n";
	//
	// 	$mailer = JFactory::getMailer();
	// 	$mailer->isHTML(true);
	// 	$config = JFactory::getConfig();
	// 	$sender = array($sender,$sendername);
	//
	// 	if ($params->get('allcerts') == true) {
	// 		$mailer->SMTPOptions = array(
	// 			'ssl' => array(
	// 			'verify_peer' => false,
	// 			'verify_peer_name' => false,
	// 			'allow_self_signed' => true
	// 			)
	// 		);
	// 	}
	//
	//
	// 	$mailer->setSender($sender);
	//
	// 	// $body   = $application_text;
	// 	// if ($body == "") {
	// 	// 	$body = JText::_('COM_CWHIRE_MAILBODY_EMPTY');
	// 	// }
	// 	$body   = $bodycode;
	// 	//   $body .= "<br>PP: ".$privacy_policy_accepted;
	// 	$mailer->setSubject(JText::_('COM_CWHIRE_MAIL_SUBJECT_PREFIX').' '.$this->item->title.', '.$this->item->location.', '.JText::_('COM_CWHIRE_JOBLISTING_REFID').' '.$this->item->refname);
	//
	// 	// $title_xml = preg_replace('/[^a-zA-Z0-9\']/', '_', $this->item->title);
	// 	// $filename_xml = $vorgangsnummer."_".$appliform_sname."-".$this->item->refname."-".$title_xml.".xml";
	// 	// $mailer->AddStringAttachment($xml_code,$filename_xml,'base64','text/xml');
	//
	// 	$attachmens = $_FILES;
	//
	// 	if(isset($attachmens['appliform_vita']['tmp_name'])) {
	// 		// trigger_error("appliform_vita upload: ".$attachmens['appliform_vita']['tmp_name'][0] , E_USER_NOTICE);
	// 		foreach ($attachmens['appliform_vita']['tmp_name'] AS $id=>$tmp_attachement) {
	// 			if(is_uploaded_file($attachmens['appliform_vita']['tmp_name'][$id])) {
	// 				$attachment1 = chunk_split(base64_encode(file_get_contents($attachmens['appliform_vita']['tmp_name'][$id])));
	// 				$filename = $vorgangsnummer."_Lebenslauf-".$id."-".$attachmens['appliform_vita']['name'][$id];
	// 				$filetype = $attachmens['appliform_vita']['type'][$id];
	// 				$vitalog .= "Vita file: ".$filename."\n";
	// 				$mailer->addAttachment($attachmens['appliform_vita']['tmp_name'][$id],$filename);
	//
	// 				if(!in_array((string)$filetype, $allowed_types))
	// 				{
	// 					$body .= "\n<p>".JText::_('COM_CWHIRE_MAIL_WARNING_CV_PREFIX')." '".$filename."' ".JText::_('COM_CWHIRE_MAIL_WARNING_DOCTYPE')."</p>\n\n";
	// 				}
	//
	// 			}
	// 		}
	// 	}
	//
	// 	if(isset($attachmens['appliform_testi']['tmp_name'])) {
	// 	foreach ($attachmens['appliform_testi']['tmp_name'] AS $id=>$tmp_attachement) {
	// 		if(is_uploaded_file($attachmens['appliform_testi']['tmp_name'][$id])) {
	// 	$attachment1 = chunk_split(base64_encode(file_get_contents($attachmens['appliform_testi']['tmp_name'][$id])));
	// 	$filename = $vorgangsnummer."_Zeugnis-".$id."-".$attachmens['appliform_testi']['name'][$id];
	// 	$filetype2 = $attachmens['appliform_testi']['type'][$id];
	// 	$testilog .= "Testi file: ".$filename."\n";
	// 	$mailer->addAttachment($attachmens['appliform_testi']['tmp_name'][$id],$filename);
	//
	// 	if(!in_array($filetype2, $allowed_types))
	// 	{
	// 			$body .= "\n<p>".JText::_('COM_CWHIRE_MAIL_WARNING_TESTI_PREFIX')." '".$filename."' ".JText::_('COM_CWHIRE_MAIL_WARNING_DOCTYPE')."</p>\n\n";
	// 	}
	// 		}
	// 	}
	// 	}
	//
	// 	if ($privacy_policy_accepted != 'on') {
	// 	$body .= "\n".JText::_('COM_CWHIRE_MAIL_WARNING_POLICY');
	// 	}
	//
	// 	// if ($mailerror == false) {
	// 		$user = JFactory::getUser();
	// 		//   $recipient = JFactory::getUser()->email;
	// 		// $recipient = "mail@createweb.de";
	// 		//  $recipient = "Markus.Bock@facts-skills.com";
	// 		//  switch ($this->item->jobcategory) {
	// 		//    case "1":
	// 		//        $recipient = "Logistik@facts-skills.com";
	// 		//        break;
	// 		//    case "2":
	// 		//        $recipient = "Technik@facts-skills.com";
	// 		//        break;
	// 		//    case "3":
	// 		//        $recipient = "Administration@facts-skills.com";
	// 		//        break;
	// 		//  }
	//
	// 		$mailer->addRecipient($recipient);
	// 		$mailer->setBody($body);
	// 		$send = $mailer->Send();
	//
	// 		$logtext = "\nSending Form:\n";
	// 		$logtext .= "Name: ".$appliform_fname." ".$appliform_sname."\n";
	// 		$logtext .= "Email: ".$appliform_mail."\n";
	// 		$logtext .= "Phone: ".$appliform_phone."\n";
	// 		$logtext .= "Attachments: ".$vitalog."\n".$testilog."\n";
	// 		$logtext .= "Recipient: ".$recipient."\n";
	// 		$logtext .= "Sent: ".$send."\n";
	//
	// 		JLog::add(JText::_($logtext), JLog::INFO, 'com_cwhire');
	//
	// 		if ( $send !== true ) {
	// 			JLog::add(JText::_($send->getMessage()), JLog::INFO, 'com_cwhire');
	// 			$this->result = '<mailresult>'.JText::_("COM_CWHIRE_MAIL_SEND_ERROR").': ' . $send->getMessage().'</mailresult>';
	// 		} else {
	// 			$this->result = '<mailresult>'.JText::_("COM_CWHIRE_MAIL_SEND_SUCCESS").'</mailresult>';
	// 		}
	// 	return $this->result;
	// }
}
