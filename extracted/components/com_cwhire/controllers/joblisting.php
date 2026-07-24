<?php
/**
 * @package    com_cwhire
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
*/

// No direct access
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/controller.php';

require_once JPATH_COMPONENT.'/helpers/cwhire.php';




/**
 * Joblisting controller class.
 */
class CwhireControllerJoblisting extends JControllerForm
{
	function getName() {
		return 'CwhireControllerJoblisting';
	}

	function send () {
		error_log("Send!", 0);
	// JLog::add(JText::_('controller send!'), JLog::INFO, 'com_cwhire');
		// $this->checkToken();

		$app    = JFactory::getApplication();
		// $model  = $this->getModel('joblisting');
		$model = JModelLegacy::getInstance('Joblisting', 'CwhireModel');
		$params = JComponentHelper::getParams('com_cwhire');
		$stub   = $this->input->getString('id');
		$cat   = $this->input->getString('jobcategory');
		$id     = (int) $stub;
		$use_captcha = $params->get('use_captcha');
		$send_copy = $params->get('send_copy');
		$confirmation_result = "";

		$SMTPOptions = array(
			'ssl' => array(
			'verify_peer' => false,
			'verify_peer_name' => false,
			'allow_self_signed' => true
			)
		);

		$helper = new JoblistingHelper();

		$used_fields = $helper->usedfields();

		$use_custom = $params->get('use_custom');
		$custom_fields = NULL;
		if ($use_custom == 1) {
		  $custom_fields = $params->get('custom_fields');
		  // error_log(print_r($custom_fields, 1), 0);
		}


		if ($use_captcha == 1) {
			JPluginHelper::importPlugin('captcha');
		}

		// JLog::add(JText::_('controller model: '.print_r($model, false)), JLog::INFO, 'com_cwhire');
		$this->item = $model->getData($id);

		// Get the data from POST
		// $request    = $this->input->post->get('jform', array(), 'array');


		// $request    = $_POST;
		$post = JFactory::getApplication()->input->post;

		// $post = JFactory::getApplication()->input;

		// JLog::add(JText::_('post: '.print_r($post, TRUE)), JLog::INFO, 'com_cwhire');


		if ($use_captcha == 1) {
			$dispatcher = JEventDispatcher::getInstance();
			// $dispatcher = JDispatcher::getInstance();
			$res = $dispatcher->trigger('onCheckAnswer',$post->get('g-recaptcha-response'));
			if(!$res[0]){
					// JLog::add(JText::_('Invalid Captcha: '.print_r($post->get('g-recaptcha-response'), TRUE)), JLog::INFO, 'com_cwhire');
					$this->result = JText::_("COM_CWHIRE_CAPTCHA_NOCONFIRM");
					$this->setRedirect(JRoute::_('index.php?option=com_cwhire&view=joblisting&id='.$this->item->id.'&sent=false&statusmessage='.$this->result, false));
					return false;
			}
		}


		$vitalog = "";
		$testilog = "";

		// $jinput->post->get('something', 'default_value', 'filter');
		$appliform_title = "";
		if (in_array("appliform_title", $used_fields)) {
			$appliform_title = $post->get('appliform_title', '', 'string');
		}

		$appliform_fname = "";
		if (in_array("appliform_fname", $used_fields)) {
			$appliform_fname = $post->get('appliform_fname', '', 'string');
		}

		$appliform_sname = "";
		if (in_array("appliform_sname", $used_fields)) {
			$appliform_sname = $post->get('appliform_sname', '', 'string');
		}

		$appliform_street = "";
		if (in_array("appliform_street", $used_fields)) {
			$appliform_street = $post->get('appliform_street', '', 'string');
		}

		$appliform_country = "Germany";
		if (in_array("appliform_country", $used_fields)) {
			error_log("appliform_country used!", 0);
			$appliform_country = $post->get('appliform_country', '', 'string');
		}

		$appliform_plz = "";
		if (in_array("appliform_plz", $used_fields)) {
			$appliform_plz = $post->get('appliform_plz', '', 'string');
		}

		$appliform_address = "";
		if (in_array("appliform_address", $used_fields)) {
			$appliform_address = $post->get('appliform_address', '', 'string');
		}

		$appliform_phone = "";
		if (in_array("appliform_phone", $used_fields)) {
			$appliform_phone = $post->get('appliform_phone', '', 'string');
		}

		$appliform_mail = "";
		if (in_array("appliform_mail", $used_fields)) {
			$appliform_mail = $post->get('appliform_mail', '', 'string');
		}

		$custom_content = Array();

		if (isset($custom_fields)) {
			foreach ($custom_fields as $key => $field) {
				if (isset($field->use_field)) {
					// $requiretext = "";
					// $requiremark = "";
					// if (isset($field->must_field)) {
					// 	$requiretext =' required="true"';
					// 	$requiremark = " *";
					//
					$custom_content[$field->fieldname] = $post->get($field->fieldname, '', 'string');
					// echo '<p class="formlabel">'.$field->title.':</p>
					// <input type="text" class="appliform_field" id="'.$field->fieldname.'"  name="'.$field->fieldname.'"'.$requiretext.'></input>'.$requiremark.'<br>';
				}
			}
		}

		$appliform_vita = $post->get('appliform_vita');
		$appliform_testi = $post->get('appliform_testi');
		$appliform_extlink = $post->get('appliform_extlink', '', 'string');
		$application_text = $post->get('application_text', '', 'string');
		$privacy_policy_accepted = $post->get('privacy_policy_accepted', '', 'bool');

		// JLog::add(JText::_('appliform_title: '.$appliform_title), JLog::INFO, 'com_cwhire');
		// JLog::add(JText::_('appliform_mail: '.$appliform_mail), JLog::INFO, 'com_cwhire');



		$vorgangsnummer = rand(1550000,1999999);

		// $app = JFactory::getApplication();
		// $params = $app->getParams();

		$usecss = $params->get('usecss');
		$doc =& JFactory::getDocument();

		if ($usecss == 1) {
		  $doc->addStyleSheet(JURI::base(true) . '/components/com_cwhire/assets/css/jobokay.css', 'text/css' );
	  }

		// $filetypes_string = $params->get('attachment-types');
		// $filetypes = explode(",", str_replace(" ", "", $filetypes_string));
		// $filetypes = $params->get('attachment-types');
		// $filetypes_string = implode(",", $filetypes);
		$model = JModelLegacy::getInstance('Joblisting', 'CwhireModel');

		$filetypes_ids = $params->get('attachment-types');
		$filetypes = $model->getExtensions($filetypes_ids);
		$allowed_types = $model->getMimetypes($filetypes_ids);
		$filetypes_string = implode(", ", $filetypes_ids);

		$sender = $params->get('sender');
		$sendername = $params->get('sendername');

		$defaultmail = $params->get('recipient');

		$recipient = $model->getRecipient($this->item->jobcategory)->recipient;
		if ($recipient == '') {
			$recipient = $defaultmail;
		}
		// JLog::add(JText::_('recipient: '.$recipient), JLog::INFO, 'com_cwhire');

		$bodycode = "<table>";
		if (in_array("appliform_title", $used_fields) || in_array("appliform_fname", $used_fields) || in_array("appliform_sname", $used_fields)) {
			$bodycode .= "
			<tr>
				<td>".JText::_('COM_CWHIRE_MAIL_NAME').": </td>
				<td>".$appliform_title." ".$appliform_fname." ".$appliform_sname."</td>
			</tr>";
		}
		if (in_array("appliform_street", $used_fields)) {
			$bodycode .= "
			<tr>
				<td>".JText::_('COM_CWHIRE_MAIL_STREET').": </td>
				<td>".$appliform_street."</td>
			</tr>";
		}
		if (in_array("appliform_plz", $used_fields) || in_array("appliform_address", $used_fields) || in_array("appliform_country", $used_fields)) {
			// error_log("appliform_country used!!!", 0);
			$bodycode .= "
			<tr>
				<td>".JText::_('COM_CWHIRE_MAIL_CITY').": </td>
				<td>".$appliform_country." ".$appliform_plz." ".$appliform_address."</td>
			</tr>";
		}
		if (in_array("appliform_phone", $used_fields)) {
			$bodycode .= "
			<tr>
				<td>".JText::_('COM_CWHIRE_MAIL_PHONE').": </td>
				<td>".$appliform_phone."</td>
			</tr>";
		}
		if (in_array("appliform_mail", $used_fields)) {
			$bodycode .= "
			<tr>
				<td>".JText::_('COM_CWHIRE_MAIL_MAIL').": </td>
				<td>".$appliform_mail."</td>
			</tr>";
		}
		if (isset($custom_fields)) {
			foreach ($custom_fields as $key => $field) {
				if (isset($field->use_field)) {
					$bodycode .= "
					<tr>
						<td>".$field->title.": </td>
						<td>".$custom_content[$field->fieldname]."</td>
					</tr>";
				}
			}
		}
		if (in_array("appliform_extlink", $used_fields)) {
			$bodycode .= "
			<tr>
				<td>".JText::_('COM_CWHIRE_MAIL_HOMEPAGE').": </td>
				<td>".$appliform_extlink."</td>
			</tr>
			</table>
			<br>";
		}
		$bodycode .= "<p>".$application_text."</p>";


		$xml_code = "<LCS-XML-Doc>
		<Geschaeftsstelle Anz=\"N\">
		<GeschSt Anz=\"1\">1</GeschSt>
		</Geschaeftsstelle>
		<Vorgangsnummer Anz=\"N\">
		<Vorgang1 Anz=\"1\"></Vorgang1>
		<Vorgang2 Anz=\"1\"></Vorgang2>
		<Kostenstvorgang Anz=\"1\">1</Kostenstvorgang>
		</Vorgangsnummer>
		<Personal Anz=\"1\">
		<PersonalNr Anz=\"1\">".$vorgangsnummer."</PersonalNr>
		<Nachname Anz=\"1\">".$appliform_sname."</Nachname>
		<Vorname Anz=\"1\">".$appliform_fname."</Vorname>
		<Kostenstpersonal Anz=\"1\"></Kostenstpersonal>
		<Titel Anz=\"1\">$appliform_title</Titel>
		<Vorsatzwort Anz=\"1\"></Vorsatzwort>
		<Namenzusatz Anz=\"1\"></Namenzusatz>
		<Adresse Anz=\"1\">
		<Wohnhaftbei Anz=\"1\"></Wohnhaftbei>
		<Strasse Anz=\"1\">".$appliform_street."</Strasse>
		<Nat Anz=\"1\">".$appliform_country."</Nat>
		<PLZ Anz=\"1\">".$appliform_plz."</PLZ>
		<Ort Anz=\"1\">".$appliform_address."</Ort>
		<Telefon1 Anz=\"1\">".$appliform_phone."</Telefon1>
		<Telefon2 Anz=\"1\"></Telefon2>
		<Telefax Anz=\"1\"></Telefax>
		<Email Anz=\"1\">".$appliform_mail."</Email>
		<Homepage Anz=\"1\">".$appliform_extlink."</Homepage>
		</Adresse>
		<GebDatum Anz=\"1\"></GebDatum>
		<GebOrt></GebOrt>
		<Geschlecht Anz=\"1\">m</Geschlecht>
		<FamilienSt Anz=\"1\">n</FamilienSt>
		<Staatsangehoerigkeit Anz=\"1\">".$appliform_country."</Staatsangehoerigkeit>
		<Bundesland Anz=\"1\"></Bundesland>
		<Gebiet></Gebiet>
		<KFZ Anz=\"N\"></KFZ>
		<Fuehrerschein Anz=\"N\"></Fuehrerschein>
		<Fuehrerschein Anz=\"N\"></Fuehrerschein>
		<Fuehrerschein Anz=\"N\"></Fuehrerschein>
		<Reisebereitschaft Anz=\"1\"></Reisebereitschaft>
		<Umzugsbereitschaft Anz=\"1\"></Umzugsbereitschaft>
		<Pendelbereitschaft Anz=\"1\"></Pendelbereitschaft>
		<Info4 Anz=\"1\"></Info4>
		<Info5 Anz=\"1\"></Info5>
		<Info6 Anz=\"1\"></Info6>
		<Freitext Anz=\"1\">".$application_text."</Freitext>
		<WVDatum Anz=\"1\"></WVDatum>
		<WVUhrzeit Anz=\"1\"></WVUhrzeit>
		<Bewerbung Anz=\"N\">
		<Bezugsquelle Anz=\"1\"></Bezugsquelle>
		<Bewerbungsdatum Anz=\"1\"></Bewerbungsdatum>
		<ErstDatum Anz=\"1\"></ErstDatum>
		<VerfuegbarAbDatum Anz=\"1\"></VerfuegbarAbDatum>
		<VerfuegbarAbText Anz=\"1\"></VerfuegbarAbText>
		<Gehaltsvorstellung Anz=\"1\"></Gehaltsvorstellung>
		</Bewerbung>
		<Kenntnis Anz=\"N\">
		<KenntnisBez Anz=\"1\"></KenntnisBez>
		<KenntnisBew Anz=\"1\"></KenntnisBew>
		</Kenntnis>
		<Kenntnis Anz=\"N\">
		<KenntnisBez Anz=\"1\"></KenntnisBez>
		<KenntnisBew Anz=\"1\"></KenntnisBew>
		</Kenntnis>
		<Kenntnissontiges Anz=\"1\"></Kenntnissontiges>
		<Fachbereich Anz=\"1\"></Fachbereich>
		<Fachbereich Anz=\"1\"></Fachbereich>
		<FachbereichSonstiges Anz=\"1\"></FachbereichSonstiges>
		<Beruf Anz=\"N\"></Beruf>
		<Beruf Anz=\"N\"></Beruf>
		<Berufsonstiges Anz=\"N\"></Berufsonstiges>
		<BerufWunsch Anz=\"N\"></BerufWunsch>
		<BerufTaetKenntnis Anz=\"N\">
		<BerufTaetKenntnisBez Anz=\"1\"></BerufTaetKenntnisBez>
		<BerufTaetKenntnisBew Anz=\"1\"></BerufTaetKenntnisBew>
		</BerufTaetKenntnis>
		<BerufTaetKenntnis Anz=\"N\">
		<BerufTaetKenntnisBez Anz=\"1\"></BerufTaetKenntnisBez>
		<BerufTaetKenntnisBew Anz=\"1\"></BerufTaetKenntnisBew>
		</BerufTaetKenntnis>
		<BerufTaetKenntnissontiges Anz=\"1\"></BerufTaetKenntnissontiges>
		<Wuensche Anz=\"1\"></Wuensche>
		<Schule Anz=\"N\">
		<Von Anz=\"1\"></Von>
		<Bis Anz=\"1\"></Bis>
		<Bezeichnung Anz=\"1\"></Bezeichnung>
		<Abschluss Anz=\"1\"></Abschluss>
		<Bemerkung Anz=\"1\"></Bemerkung>
		</Schule>
		<Ausbildung Anz=\"N\">
		<Von Anz=\"1\"></Von>
		<Bis Anz=\"1\"></Bis>
		<Betrieb Anz=\"1\"></Betrieb>
		<Abschluss Anz=\"1\"></Abschluss>
		<Bemerkung Anz=\"1\"></Bemerkung>
		</Ausbildung>
		<Beschaeftigung Anz=\"N\">
		<Von Anz=\"1\"></Von>
		<Bis Anz=\"1\"></Bis>
		<Betrieb Anz=\"1\"></Betrieb>
		<BeschBeruf Anz=\"1\"></BeschBeruf>
		<Bemerkung Anz=\"1\"></Bemerkung>
		</Beschaeftigung>
		<Bildung Anz=\"N\">
		<Von Anz=\"1\"></Von>
		<Bis Anz=\"1\"></Bis>
		<Bezeichnung Anz=\"1\"></Bezeichnung>
		<Zertifikat Anz=\"1\"></Zertifikat>
		<Bemerkung Anz=\"1\"></Bemerkung>
		</Bildung>
		<LebenSonstiges Anz=\"1\">
		<Vollzeit Anz=\"1\"></Vollzeit>
		<Teilzeit Anz=\"1\"></Teilzeit>
		</LebenSonstiges>
		</Personal>
		</LCS-XML-Doc>\n";

		$mailer = JFactory::getMailer();
		$mailer->isHTML(true);
		$config = JFactory::getConfig();
		$sender = array($sender,$sendername);

		if ($params->get('allcerts') == true) {
			$mailer->SMTPOptions = $SMTPOptions;
			// $mailer->SMTPOptions = array(
			// 	'ssl' => array(
			// 	'verify_peer' => false,
			// 	'verify_peer_name' => false,
			// 	'allow_self_signed' => true
			// 	)
			// );
		}


		$mailer->setSender($sender);

		// $body   = $application_text;
		// if ($body == "") {
		// 	$body = JText::_('COM_CWHIRE_MAILBODY_EMPTY');
		// }
		$body   = $bodycode;

		//   $body .= "<br>PP: ".$privacy_policy_accepted;
		$mailer->setSubject(JText::_('COM_CWHIRE_MAIL_SUBJECT_PREFIX').' '.$this->item->title.', '.$this->item->location.', '.JText::_('COM_CWHIRE_JOBLISTING_REFID').' '.$this->item->refname);

		// $title_xml = preg_replace('/[^a-zA-Z0-9\']/', '_', $this->item->title);
		// $filename_xml = $vorgangsnummer."_".$appliform_sname."-".$this->item->refname."-".$title_xml.".xml";
		// $mailer->AddStringAttachment($xml_code,$filename_xml,'base64','text/xml');

		$attachmens = $_FILES;

		if(isset($attachmens['appliform_vita']['tmp_name'])) {
			// trigger_error("appliform_vita upload: ".$attachmens['appliform_vita']['tmp_name'][0] , E_USER_NOTICE);
			foreach ($attachmens['appliform_vita']['tmp_name'] AS $id=>$tmp_attachement) {
				if(is_uploaded_file($attachmens['appliform_vita']['tmp_name'][$id])) {
					$attachment1 = chunk_split(base64_encode(file_get_contents($attachmens['appliform_vita']['tmp_name'][$id])));
					$filename = $vorgangsnummer."_Lebenslauf-".$id."-".$attachmens['appliform_vita']['name'][$id];
					$filetype = $attachmens['appliform_vita']['type'][$id];
					$vitalog .= "Vita file: ".$filename."\n";
					$mailer->addAttachment($attachmens['appliform_vita']['tmp_name'][$id],$filename);

					// if(!in_array((string)$filetype, $allowed_types))
					// {
					// 	$body .= "\n<p>".JText::_('COM_CWHIRE_MAIL_WARNING_CV_PREFIX')." '".$filename."' ".JText::_('COM_CWHIRE_MAIL_WARNING_DOCTYPE')."</p>\n\n";
					// }

				}
			}
		}

		if(isset($attachmens['appliform_testi']['tmp_name'])) {
		foreach ($attachmens['appliform_testi']['tmp_name'] AS $id=>$tmp_attachement) {
			if(is_uploaded_file($attachmens['appliform_testi']['tmp_name'][$id])) {
		$attachment1 = chunk_split(base64_encode(file_get_contents($attachmens['appliform_testi']['tmp_name'][$id])));
		$filename = $vorgangsnummer."_Zeugnis-".$id."-".$attachmens['appliform_testi']['name'][$id];
		$filetype2 = $attachmens['appliform_testi']['type'][$id];
		$testilog .= "Testi file: ".$filename."\n";
		$mailer->addAttachment($attachmens['appliform_testi']['tmp_name'][$id],$filename);

		// if(!in_array($filetype2, $allowed_types))
		// {
		// 		$body .= "\n<p>".JText::_('COM_CWHIRE_MAIL_WARNING_TESTI_PREFIX')." '".$filename."' ".JText::_('COM_CWHIRE_MAIL_WARNING_DOCTYPE')."</p>\n\n";
		// }
			}
		}
		}

		if ($privacy_policy_accepted != 'on') {
			$body .= "\n".JText::_('COM_CWHIRE_MAIL_WARNING_POLICY');
		}

			$mailer->addRecipient($recipient);
			$mailer->setBody($body);
			$send = $mailer->Send();

			// $logtext = "\nSending Form:\n";
			// $logtext .= "Name: ".$appliform_fname." ".$appliform_sname."\n";
			// $logtext .= "Email: ".$appliform_mail."\n";
			// $logtext .= "Phone: ".$appliform_phone."\n";
			// $logtext .= "Attachments: ".$vitalog."\n".$testilog."\n";
			// $logtext .= "Recipient: ".$recipient."\n";
			// $logtext .= "Sent: ".$send."\n";

			if ( $send !== true) {
				// JLog::add(JText::_('error: '.$sendmessage), JLog::INFO, 'com_cwhire');
				// JLog::add(JText::_('error: '.$logtext), JLog::INFO, 'com_cwhire');
				$this->result = JText::_("COM_CWHIRE_MAIL_SEND_ERROR").": ".$send;
			} else {
				if ($send_copy == 1) {
					$confirmation_text = JText::_('COM_CWHIRE_CONFIRMATION_NOTE');
					$confirmation_body = $confirmation_text."<br><br>".$bodycode;

					$mailer = JFactory::getMailer();
					$mailer->isHTML(true);
					if ($params->get('allcerts') == true) {
						$mailer->SMTPOptions = $SMTPOptions;
					}
					$mailer->addRecipient($appliform_mail);
					$mailer->setSubject(JText::_('COM_CWHIRE_CONFIRMATION_SUBJECT').' '.$this->item->title.', '.$this->item->location.', '.JText::_('COM_CWHIRE_JOBLISTING_REFID').' '.$this->item->refname);
					$mailer->setBody($confirmation_body);
					$send_confirmation = $mailer->Send();
					error_log("N message: ".$send_confirmation, 0);
					if ( $send_confirmation !== true) {
						$confirmation_result = " - ".JText::_("COM_CWHIRE_CONFIRMATION_SEND_ERROR").": ".$send_confirmation;
					}
				}
				// JLog::add(JText::_('success: '.$sendmessage), JLog::INFO, 'com_cwhire');
				// JLog::add(JText::_('success: '.$logtext), JLog::INFO, 'com_cwhire');
				$this->result = JText::_("COM_CWHIRE_MAIL_SEND_SUCCESS").$confirmation_result;
			}
			// JLog::add(JText::_('ID: '.$this->item->id), JLog::INFO, 'com_cwhire');
			$this->setRedirect(JRoute::_('index.php?option=com_cwhire&view=joblisting&id='.$this->item->id.'&sent=true&statusmessage='.$this->result, false));
			return false;
		// return $this->result;
	}


	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 *
	 * @since	1.6
	 */
	// public function edit()
	// {
	// 	$app			= JFactory::getApplication();
	//
	// 	// Get the previous edit id (if any) and the current edit id.
	// 	$previousId = (int) $app->getUserState('com_cwhire.edit.joblisting.id');
	// 	$editId	= JFactory::getApplication()->input->getInt('id', null, 'array');
	//
	// 	// Set the user id for the user to edit in the session.
	// 	$app->setUserState('com_cwhire.edit.joblisting.id', $editId);
	//
	// 	// Get the model.
	// 	$model = $this->getModel('Joblisting', 'CwhireModel');
	//
	// 	// Check out the item
	// 	if ($editId) {
  //           $model->checkout($editId);
	// 	}
	//
	// 	// Check in the previous user.
	// 	if ($previousId) {
  //           $model->checkin($previousId);
	// 	}
	//
	// 	// Redirect to the edit screen.
	// 	$this->setRedirect(JRoute::_('index.php?option=com_cwhire&view=joblistingform&layout=edit', false));
	// }

	/**
	 * Method to save a user's profile data.
	 *
	 * @return	void
	 * @since	1.6
	 */
	// public function save()
	// {
	// 	// Check for request forgeries.
	// 	JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
	//
	// 	// Initialise variables.
	// 	$app	= JFactory::getApplication();
	// 	$model = $this->getModel('Joblisting', 'CwhireModel');
	//
	// 	// Get the user data.
	// 	$data = JFactory::getApplication()->input->get('jform', array(), 'array');
	//
	// 	// Validate the posted data.
	// 	$form = $model->getForm();
	// 	if (!$form) {
	//
	// 		JFactory::getApplication()->enqueueMessage($model->getError(), 'error');
	// 		return false;
	// 	}
	//
	// 	// Validate the posted data.
	// 	$data = $model->validate($form, $data);
	//
	// 	// Check for errors.
	// 	if ($data === false) {
	// 		// Get the validation messages.
	// 		$errors	= $model->getErrors();
	//
	// 		// Push up to three validation messages out to the user.
	// 		for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
	// 			if ($errors[$i] instanceof Exception) {
	// 				$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
	// 			} else {
	// 				$app->enqueueMessage($errors[$i], 'warning');
	// 			}
	// 		}
	//
	// 		// Save the data in the session.
	// 		$app->setUserState('com_cwhire.edit.joblisting.data', $_POST['jform']);
	//
	// 		// Redirect back to the edit screen.
	// 		$id = (int) $app->getUserState('com_cwhire.edit.joblisting.id');
	// 		$this->setRedirect(JRoute::_('index.php?option=com_cwhire&view=joblisting&layout=edit&id='.$id, false));
	// 		return false;
	// 	}
	//
	// 	// Attempt to save the data.
	// 	$return	= $model->save($data);
	//
	// 	// Check for errors.
	// 	if ($return === false) {
	// 		// Save the data in the session.
	// 		$app->setUserState('com_cwhire.edit.joblisting.data', $data);
	//
	// 		// Redirect back to the edit screen.
	// 		$id = (int)$app->getUserState('com_cwhire.edit.joblisting.id');
	// 		$this->setMessage(JText::sprintf('Save failed', $model->getError()), 'warning');
	// 		$this->setRedirect(JRoute::_('index.php?option=com_cwhire&view=joblisting&layout=edit&id='.$id, false));
	// 		return false;
	// 	}
	//
	//
  //       // Check in the profile.
  //       if ($return) {
  //           $model->checkin($return);
  //       }
	//
  //       // Clear the profile id from the session.
  //       $app->setUserState('com_cwhire.edit.joblisting.id', null);
	//
  //       // Redirect to the list screen.
  //       $this->setMessage(JText::_('COM_CWHIRE_ITEM_SAVED_SUCCESSFULLY'));
  //       $menu = & JSite::getMenu();
  //       $item = $menu->getActive();
  //       $this->setRedirect(JRoute::_($item->link, false));
	//
	// 	// Flush the data from the session.
	// 	$app->setUserState('com_cwhire.edit.joblisting.data', null);
	// }


    // function cancel() {
		// $menu = & JSite::getMenu();
    //     $item = $menu->getActive();
    //     $this->setRedirect(JRoute::_($item->link, false));
    // }

	// public function remove()
	// {
	// 	// Check for request forgeries.
	// 	JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
	//
	// 	// Initialise variables.
	// 	$app	= JFactory::getApplication();
	// 	$model = $this->getModel('Joblisting', 'CwhireModel');
	//
	// 	// Get the user data.
	// 	$data = JFactory::getApplication()->input->get('jform', array(), 'array');
	//
	// 	// Validate the posted data.
	// 	$form = $model->getForm();
	// 	if (!$form) {
	//
	// 		JFactory::getApplication()->enqueueMessage($model->getError(), 'error');
	// 		return false;
	// 	}
	//
	// 	// Validate the posted data.
	// 	$data = $model->validate($form, $data);
	//
	// 	// Check for errors.
	// 	if ($data === false) {
	// 		// Get the validation messages.
	// 		$errors	= $model->getErrors();
	//
	// 		// Push up to three validation messages out to the user.
	// 		for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
	// 			if ($errors[$i] instanceof Exception) {
	// 				$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
	// 			} else {
	// 				$app->enqueueMessage($errors[$i], 'warning');
	// 			}
	// 		}
	//
	// 		// Save the data in the session.
	// 		$app->setUserState('com_cwhire.edit.joblisting.data', $data);
	//
	// 		// Redirect back to the edit screen.
	// 		$id = (int) $app->getUserState('com_cwhire.edit.joblisting.id');
	// 		$this->setRedirect(JRoute::_('index.php?option=com_cwhire&view=joblisting&layout=edit&id='.$id, false));
	// 		return false;
	// 	}
	//
	// 	// Attempt to save the data.
	// 	$return	= $model->delete($data);
	//
	// 	// Check for errors.
	// 	if ($return === false) {
	// 		// Save the data in the session.
	// 		$app->setUserState('com_cwhire.edit.joblisting.data', $data);
	//
	// 		// Redirect back to the edit screen.
	// 		$id = (int)$app->getUserState('com_cwhire.edit.joblisting.id');
	// 		$this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
	// 		$this->setRedirect(JRoute::_('index.php?option=com_cwhire&view=joblisting&layout=edit&id='.$id, false));
	// 		return false;
	// 	}
	//
	//
  //       // Check in the profile.
  //       if ($return) {
  //           $model->checkin($return);
  //       }
	//
  //       // Clear the profile id from the session.
  //       $app->setUserState('com_cwhire.edit.joblisting.id', null);
	//
  //       // Redirect to the list screen.
  //       $this->setMessage(JText::_('COM_CWHIRE_ITEM_DELETED_SUCCESSFULLY'));
  //       $menu = & JSite::getMenu();
  //       $item = $menu->getActive();
  //       $this->setRedirect(JRoute::_($item->link, false));
	//
	// 	// Flush the data from the session.
	// 	$app->setUserState('com_cwhire.edit.joblisting.data', null);
	// }


}
