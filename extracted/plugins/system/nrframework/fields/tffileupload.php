<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Utility\Utility;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Uri\Uri;
use NRFramework\File;

class JFormFieldTFFileUpload extends TextField
{
    /**
     *  Method to render the input field
     *
     *  @return  string
     */
    protected function getInput()
    {
        $ajax_url = isset($this->element['ajax_url']) ? (string) $this->element['ajax_url'] : null;
        if (!$ajax_url)
        {
            return Text::_('NR_FILE_UPLOAD_AJAX_URL_NOT_SET');
        }

        $this->assets();

        $limit_files = (int) $this->element['limit_files'] ?? 5;

        $dataAttributes = isset($this->element['data_attributes']) ? json_decode((string) $this->element['data_attributes'], true) : [];

        $max_file_size = (int) $this->element['max_file_size'];
        if ($max_file_size === 0)
        {
            $max_file_size = Utility::getMaxUploadSize() / 1024 / 1024;
        }

        $preview = (string) $this->element['preview'] === '1';

        $class = $limit_files !== 1 ? 'multiple' : '';

        if (!$preview)
        {
            $class .= ' no-preview';
        }

        $payload = [
            'id'                => $this->id,
            'name'              => $this->name,
            'preview'           => $preview,
            'max_file_size'     => $max_file_size,
            'limit_files'       => $limit_files,
            'upload_types'      => (string) $this->element['upload_types'] ?? '*',
            'class'             => $class,
            'ajax_url'          => $ajax_url,
            'dataAttributes'    => $dataAttributes,
            'value'             => $this->value
        ];

        Text::script('NR_FILE_UPLOAD_FALLBACK_MESSAGE');
        Text::script('NR_FILE_UPLOAD_INVALID_FILE');
        Text::script('NR_FILE_UPLOAD_FILETOOBIG');
        Text::script('NR_FILE_UPLOAD_RESPONSE_ERROR');
        Text::script('NR_CANCEL_UPLOAD');
        Text::script('NR_FILE_UPLOAD_CANCEL_UPLOAD_CONFIRMATION');
        Text::script('NR_REMOVE_FILE');
        Text::script('NR_FILE_UPLOAD_MAX_FILES_EXCEEDED');
        Text::script('NR_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_ITEM');
        Text::script('NR_FILE_UPLOAD_FILE_MISSING');
        
        $layout = new FileLayout('fileupload', JPATH_PLUGINS . '/system/nrframework/layouts');
        return $layout->render($payload);
    }

    /**
     * Handle file upload to temporary location.
     * Extensions can override this behavior by providing their own ajax_url
     *
     * @param   Registry  $options
     *
     * @return  void
     */
    public function onUpload($options)
    {
        // Get file from request
        $file = \Joomla\CMS\Factory::getApplication()->input->files->get('file', null, 'cmd');
        if (!$file)
        {
            $this->uploadDie('NR_FILE_UPLOAD_INVALID_FILE');
        }

        // Handle multiple file uploads - get first file from array if needed
        $first_property = array_pop($file);
        if (is_array($first_property))
        {
            $file = $first_property;
        }

        try 
        {
            $allowedTypes = $options->get('upload_types', '*');
        
            // Upload to temp folder with framework defaults
            $uploaded_filename = File::upload($file, null, $allowedTypes);

            // Return success response
            echo json_encode([
                'file' => base64_encode($uploaded_filename),
                'file_encode' => base64_encode(str_replace([JPATH_SITE, JPATH_ROOT], '', $uploaded_filename))
            ]);
            exit;
        } 
        catch (\Throwable $th) 
        {
            $this->uploadDie($th->getMessage());
        }
    }

    /**
     * Handle file deletion from temporary location.
     * Extensions can override this behavior by providing their own ajax_url.
     * 
     * @return  void
     */
    public function onDelete()
    {
		$filename = \Joomla\CMS\Factory::getApplication()->input->getString('filename', null, 'base64');
		if (!$filename)
		{
			$this->uploadDie('COM_CONVERTFORMS_UPLOAD_ERROR_INVALID_FILE');
		}

		// Delete the file
		try {
			return ['success' => File::delete(base64_decode($filename))];
		} catch (\Throwable $th)
		{
			$this->uploadDie($th->getMessage());
		}
    }

    /**
     * Handle upload errors with proper HTTP response
     *
     * @param  string $error_message
     *
     * @return void
     */
    private function uploadDie($error_message)
    {
        http_response_code('500');
        die(Text::_($error_message));
    }

    private function assets()
    {
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        
        // Load Sortable
        $limit_files = (int) $this->element['limit_files'] ?? 5;
        if ($limit_files !== 1)
        {
            $wa->registerScript('plg_system_nrframework.sortable', 'plg_system_nrframework/vendor/sortable.min.js', []);
            $wa->useScript('plg_system_nrframework.sortable');
        }

        // Register framework's dropzone vendor library
        $wa->registerScript('plg_system_nrframework.dropzone', 'plg_system_nrframework/vendor/dropzone.min.js', [], ['defer' => true], []);
        
        // Register framework's fileupload CSS
        $wa->registerStyle('plg_system_nrframework.fileupload.css', 'plg_system_nrframework/fileupload.css', [], [], []);
        
        // Register framework's fileupload instance with dropzone dependency
        $wa->registerScript(
            'plg_system_nrframework.fileupload.instance', 
            'plg_system_nrframework/fileupload/instance.js', 
            [], 
            [], 
            ['plg_system_nrframework.dropzone']  // Dependency on dropzone
        );
        
        // Register framework's fileupload JS with dropzone dependency
        $wa->registerScript(
            'plg_system_nrframework.fileupload.initiator', 
            'plg_system_nrframework/fileupload/initiator.js', 
            [], 
            [], 
            ['plg_system_nrframework.fileupload.instance']  // Dependency on dropzone
        );
        
        // Load all assets
        $wa->useScript('plg_system_nrframework.dropzone');
        $wa->useStyle('plg_system_nrframework.fileupload.css');
        $wa->useScript('plg_system_nrframework.fileupload.instance');
        $wa->useScript('plg_system_nrframework.fileupload.initiator');
    }
}