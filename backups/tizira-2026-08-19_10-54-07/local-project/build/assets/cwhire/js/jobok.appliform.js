(function( $ ) {
  /*
  /* Only call from document.ready function!
  */
  var baseColor = '#07c90f';
  $.fn.appform = function( options ) {
    var that = this;
    var defaults = {
      appliform : '#application_form',
      titleVita : 'Upload vita',
      titleTesti : 'Upload Testimonial'
    }
    var settings = $.extend( {}, defaults, options );
    // this.extrasytle = function() {
    //   this.css({ 'font-style' : 'italic'});
    //   return this;
    // }
    // this.css({
    //   color : settings.color,
    //   'font-weight' : settings.fontWeight
    // });




    var textarea_clicked = false;
    var vita_count = 1;
    var testi_count = 1;
    // $(document).ready( function() {
    this.hide();
    $('#uploadtype1').click();
    // $('#form_linkgroup').hide();
    $('#formhider').click( function () {
      if (formHidden) {
        // 	      alert('click!');
        that.slideDown(500);
        // $('#formhider img').attr('src','/../media/com_jobokay/images/arrow_down.png');
        $('#formhider').addClass('active');
        formHidden = false;
        // setTimeout( function() {
        //   $("html, body").animate({ scrollTop: $(document).height() }, 10);
        // },500);

      }
      else {
        that.slideUp(500);
        // $('#formhider img').attr('src','/../media/com_jobokay/images/arrow_right.png');
        $('#formhider').removeClass('active');
        formHidden = true;
      }
      return false;
    });
    $('input:radio[name=uploadtype]').change(
      function () {
        var choice = $('input:radio[name=uploadtype]:checked').val();
        // 		alert(choice);
        if (choice == 'upld') {
          $('#form_linkgroup').hide();
          $('#form_uploadgroup').show();
        }
        else {
          $('#form_linkgroup').show();
          $('#form_uploadgroup').hide();
        }
      }
    );
    $('#formlabel_upload').click( function () {
      // 		$('#uploadtype1').attr('checked', 'checked');
      $('#uploadtype1').click();
    });
    $('#formlabel_link').click( function () {
      $('#uploadtype2').click();
      // 		alert('uploadtype2');
      // 		$('#uploadtype2').attr('checked', 'checked');
    });

    $('#application_text').click( function () {
      if (textarea_clicked == false) {
        // 		$('#uploadtype1').attr('checked', 'checked');
        $('#application_text').html('');
        $('#application_text').css({'font-style' : 'normal'});
        textarea_clicked = true;
      }
    });

    $('#appliform_vita_'+vita_count).change( function () { that.vita_changed(this) });

    // 	    $('#button_clr_'+vita_count).click( function () { if(vita_count != 1) { $('#appliform_vita_'+vita_count).remove() } });


    $('#appliform_testi_'+testi_count).change( function () { that.testi_changed(this) });

    // 	    $('#button_clr_'+testi_count).click( function () { if(testi_count != 1) { $('#appliform_testi_'+testi_count).remove() } });

    var bar = $('.bar');
    var percent = $('.percent');
    var status = $('#status');


    this.ajaxForm({
      beforeSubmit: function() {

		    if (checkMail() == true) {
          $('#button_submit').attr('disabled', '');
          status.empty();
          status.html('<b>'+Joomla.JText._('COM_CWHIRE_MAIL_TRANSMISSION_PROGRESS')+'</b>');
          var percentVal = '0%';
          bar.width(percentVal)
          percent.html(percentVal);
          return true;
		    }
        return false;
      },
      uploadProgress: function(event, position, total, percentComplete) {
        var percentVal = percentComplete + '%';
        bar.width(percentVal)
        percent.html(percentVal);
      },
      success: function() {
        var percentVal = '100%';
        bar.width(percentVal)
        percent.html(percentVal);
      },
      complete: function(xhr) {
        console.log('responseText: '+xhr.responseText);
        var res = xhr.responseText.match(/<mailresult>.*?<\/mailresult>/g);
        // status.html('<b>'+res[1]+'</b>');
        if (res) {
          status.html('<b>'+res[0]+'</b>');
        }
        else {
          var result = '<mailresult>'+Joomla.JText._('COM_CWHIRE_MAIL_SEND_SUCCESS')+'</mailresult>';
          status.html('<b>'+result+'</b>');
          // status.html('<b>SENT!</b>');
        }
        // 			status.html('Ergebnis: '+xhr.responseText);
        // 			status.html('<b>Vielen Dank, Ihre Bewerbung wurde an uns versendet.</b>');
        return true;
      }
    });

    this.vita_changed = function(jqobject) {
      // 		nextct = vita_count + 1;
      following_element = parseInt($(jqobject).attr('vita_count'))+1;
      if ($('#appliform_vita_'+following_element).length == 0){
        // 		    alert('appliform_vita_'+following_element+' don\'t exist!');
        vita_count += 1;
        // 		$('#uploadtype1').attr('checked', 'checked');
        // 		$('#uploadtype1').click();

        $('#form_uploadgroup_vita').append('<br><p class="formlabel">'+ settings.titleVita +' '+vita_count+':</p> \
        <input type="file" class="appliform_upload appliform_vita" id="appliform_vita_'+vita_count+'"  name="appliform_vita[]" size="40"  multiple="multiple"></input>\
        <!--<input type="button" id="button_clr_'+vita_count+'" name="button_clr" value="X"></input>-->');
        // 		    $('#button_clr_'+vita_count).click( function () { if(vita_count != 1) { $('#appliform_vita_'+vita_count).remove() } });
        $('#appliform_vita_'+vita_count).attr('vita_count', vita_count);
        // 		    $('#appliform_vita_'+vita_count).change( vita_changed );
        $('#appliform_vita_'+vita_count).change( function () { that.vita_changed(this) });
      }
    }

    this.testi_changed = function(jqobject) {
      following_element = parseInt($(jqobject).attr('testi_count'))+1;
      if ($('#appliform_testi_'+following_element).length == 0){
        testi_count += 1;
        $('#form_uploadgroup_testi').append('<br><p class="formlabel">'+ settings.titleTesti +' '+testi_count+':</p> \
        <input type="file" class="appliform_upload appliform_testi" id="appliform_testi_'+testi_count+'"  name="appliform_testi[]" size="40"  multiple="multiple"></input>\
        <!--<input type="button" id="button_clr_'+testi_count+'" name="button_clr" value="X"></input>-->');
        $('#appliform_testi_'+testi_count).attr('testi_count', testi_count);
        $('#appliform_testi_'+testi_count).change( function () { that.testi_changed(this) });
      }
    }

    var formHidden = true;
    return this;
  }
} (jQuery));
