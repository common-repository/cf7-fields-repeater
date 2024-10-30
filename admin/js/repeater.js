jQuery(document).ready(function(){
  if(jQuery('ul#contact-form-editor-tabs li#repeater-panel-tab').hasClass('ui-state-active')){
    jQuery('#form-panel textarea#wpcf7-form').attr("id", "wpcf7-temp-form");
    jQuery('#repeater-panel textarea.wpcf7-repeater-textarea').attr("id", null);
    jQuery('#repeater-panel textarea.wpcf7-repeater-textarea').eq(0).attr("id", "wpcf7-form");
  }
  jQuery('#repeater-panel #tag-generator-list a').each(function(){
    if(jQuery(this).attr("href").indexOf('repeater') > 0){
      jQuery(this).hide();
    }
  });
  jQuery('ul#contact-form-editor-tabs li').click(function(){
    if(jQuery(this).hasClass('ui-state-active') && jQuery(this).attr('id') == 'repeater-panel-tab'){
      jQuery('#form-panel textarea#wpcf7-form').attr("id", "wpcf7-temp-form");
      jQuery('#repeater-panel textarea.wpcf7-repeater-textarea').attr("id", null);
      jQuery('#repeater-panel textarea.wpcf7-repeater-textarea').eq(0).attr("id", "wpcf7-form");
    }else{
      jQuery('#form-panel textarea#wpcf7-temp-form').attr("id", "wpcf7-form");
      jQuery('#repeater-panel textarea.wpcf7-repeater-textarea').attr("id", null);
    }
  });
  jQuery(".wpcf7-repeater-add").click(function(){
    var html = jQuery('#wpcf7-repeater-item').html().replace(/{{index}}/g, _repeater_index);
    jQuery('#wpcf7-repeater-list').append('<div class="wpcf7-repeater-item">'+html+'</div>');
    _repeater_index ++;
    return false;
  });

  jQuery("body").on("click", ".wpcf7-repeater-remove", function(){
    jQuery(this).closest('.wpcf7-repeater-item').remove();
    _repeater_index = _repeater_index -1;
    return false;
  });

  jQuery("body").on("focus", "textarea.wpcf7-repeater-textarea", function(){
    jQuery('#form-panel textarea#wpcf7-form').attr("id", "wpcf7-temp-form");
    jQuery('#repeater-panel textarea.wpcf7-repeater-textarea').attr("id", null);
    jQuery(this).attr("id", "wpcf7-form");
    return false;
  });

});
