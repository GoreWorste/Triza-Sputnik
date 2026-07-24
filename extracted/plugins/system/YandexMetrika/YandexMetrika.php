<?php
/**
* @version		$version 1.1 Nikita Zonov  $
* @copyright	Copyright (C) 2012 Joomalungma. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Updated		22th Dec 2012
*
* Twitter: @joomalungma
* Email: info@joomalungma.com
*
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin');

class plgSystemYandexMetrika extends JPlugin
{
	function plgYandexMetrika(&$subject, $config)
	{		
		parent::__construct($subject, $config);
		$this->_plugin = JPluginHelper::getPlugin( 'system', 'YandexMetrika' );
		$this->_params = new JParameter( $this->_plugin->params );
	}
	
	function onAfterRender()
	{
        $app = JFactory::getApplication();
        if($app->isAdmin())
        {
            return;
        }
		// Initialise variables
        $id = $this->params->get( 'id', '' );
        $webvisor = $this->params->get( 'webvisor', '' );
		$clickMap = $this->params->get( 'clickMap', '' );
        $linksOut = $this->params->get( 'linksOut', '' );
        $accurateTrackBounce = $this->params->get( 'accurateTrackBounce', '' );
        $noIndex = $this->params->get( 'noIndex', '' );
        $noIndexWrapper = $this->params->get( 'noindexWrapper', '1' );


		

		
		//getting body code and storing as buffer
		$buffer = JResponse::getBody();
		
		//embed Yandex Metrika code
        $webvisor = $webvisor ? 'true' : 'false';
        $clickMap = $clickMap ? 'true' : 'false';
        $linksOut = $linksOut ? 'true' : 'false';
        $accurateTrackBounce = $accurateTrackBounce ? 'true' : 'false';
        $noIndex = $noIndex ? 'ut:"noindex",' : '';

        $javascript = '<!-- Yandex.Metrika counter --><script type="text/javascript">(function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter' . $id . ' = new Ya.Metrika({id:' . $id . ', clickmap:' . $clickMap . ', trackLinks:' . $linksOut . ', accurateTrackBounce:' . $accurateTrackBounce  . ','. $noIndex . ' webvisor:' . $webvisor . '}); } catch(e) {} }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f); } else { f(); } })(document, window, "yandex_metrika_callbacks");</script><noscript><div><img src="//mc.yandex.ru/watch/' . $id . '" style="position:absolute; left:-9999px;" alt="" /></div></noscript><!-- /Yandex.Metrika counter -->';

        if($noIndexWrapper) $javascript = '<!--noindex-->' . $javascript . '<!--/noindex-->';

		$buffer = preg_replace ("/<\/body>/", $javascript."\n\n</body>", $buffer);
		
		//output the buffer
		JResponse::setBody($buffer);
		
		return true;
	}
}
?>
