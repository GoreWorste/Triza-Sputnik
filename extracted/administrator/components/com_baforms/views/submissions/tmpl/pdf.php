<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;
// load tooltip behavior
JHtml::_('behavior.tooltip');

$print = explode('_-_', $this->print);
?>
<link rel="stylesheet" href="components/com_baforms/assets/css/ba-admin.css" type="text/css"/>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.5/jspdf.min.js"></script>

<script type="text/javascript">	
var $ = jQuery;
document.addEventListener('DOMContentLoaded', function(){
    var quotes = document.getElementById('pdf-wrapper');
    html2canvas(quotes, {
        onrendered: function(canvas) {
            var pdf = new jsPDF('p', 'pt', 'letter');
            for (var i = 0; i <= quotes.clientHeight / 1130; i++) {
                var onePageCanvas = document.createElement("canvas"),
                    ctx = onePageCanvas.getContext('2d'),
                    dHeight = 1130;
                if (quotes.clientHeight - 1130 * i < 1130) {
                    dHeight = quotes.clientHeight - 1130 * i;
                }
                onePageCanvas.setAttribute('width', 900);
                onePageCanvas.setAttribute('height', dHeight);
                ctx.drawImage(canvas, 0, 1130 * i, 900, dHeight, 0, 0, 900, dHeight);
                var canvasDataURL = onePageCanvas.toDataURL("image/jpeg", 1.0),
                    width = onePageCanvas.width,
                    height = onePageCanvas.clientHeight;
                if (i > 0) {
                    pdf.addPage(612, 791);
                }
                pdf.setPage(i+1);
                pdf.addImage(canvasDataURL, 'jpeg', 20, 40, (width*.62), (height*.62));
            }
            pdf.save('baforms.pdf');
        }
    });
});

</script>
<div id="pdf-wrapper" style="background-color: #ffffff;">
    <h1><?php echo $this->printTitle->title; ?></h1><br>
    <p><?php echo $this->printTitle->date_time; ?></p>
    <div class="row-fluid">
        <div class="span6">
            <table id="submission-data" class="table table-striped">
                <?php foreach ($print as $message) { ?>
                <?php $message = explode('|-_-|', $message); ?>
                <?php if (!empty($message) && isset($message[2]) && $message[2] != 'terms') { ?>
                <tr>
                    <td style="width:30%"><b><?php echo $message[0]; ?>:</b></td>
                    <td>
                        <?php if ($message[2] != 'upload') { ?>
                            <?php $message[1] = str_replace('<br>', '', $message[1]); ?>
                            <p><?php echo $message[1]; ?></p>
                        <?php } else if (!empty($message[1])) { ?>
                            <a target="_blank" href="<?php echo JUri::root().$this->uploaded_path; ?>/baforms/<?php echo $message[1]; ?>">
                                View Uploaded File
                            </a>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
                <?php } ?>
            </table>
        </div>
    </div>
</div>