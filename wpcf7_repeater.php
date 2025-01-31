<?php

/*
Plugin Name: Cf7 Fields Repeater
Plugin URI: http://www.ajaxy.org/product/contact-form-7-repeater
Description: Repeats Contact Form 7 fields with an ease.
Author: Naji Amer - @n-for-all
Author URI: http://ajaxy.org/
Text Domain: cf7-fields-repeater
Version: 2.0.3
*/

if (is_admin()) {
    require_once 'admin/license.php';
}

/* Not tested with older versions of wpcf7, lower the version number at your own risk */
define("WPCF7_REQUIRED_VERSION", "4.4");


define("WPCF7_REPEATER_TEXT_DOMAIN", "cf7-fields-repeater");
define("WPCF7_REPEATER_PLUGIN_URL", plugins_url('', __FILE__));

class WPCF7_Repeater
{
    private $license = null;
    public function __construct()
    {
        global $AJAXY_CF7_Repeater_License;
        $this->license = $AJAXY_CF7_Repeater_License;
        add_action('wpcf7_init', array(&$this, 'wpcf7_init'));
    }
    public function wpcf7_init()
    {
        if (version_compare(WPCF7_VERSION, WPCF7_REQUIRED_VERSION) >= 0) {
            wpcf7_add_shortcode('repeater', array(&$this, 'shortcode_handler'));
            $this->filters();
            $this->actions();
        } else {
            add_action('admin_notices', array(&$this, 'version_notice__error'));
        }
    }
    public function filters()
    {
        add_filter('wpcf7_editor_panels', array(&$this, 'editor_panels'), 10, 1);
        add_filter('wpcf7_contact_form_properties', array(&$this, 'properties'), 10, 2);
        add_filter('wpcf7_validate_repeater', array(&$this, 'validation_filter'), 10, 2);
        add_filter('wpcf7_special_mail_tags', array(&$this, 'wpcf7_special_mail_tags'), 10, 3);
    }
    public function actions()
    {
        add_action('wpcf7_save_contact_form', array(&$this, 'save_repeater'), 10, 1);
        add_action('admin_enqueue_scripts', array(&$this, 'admin_scripts'));

        if (is_admin()) {
            add_action('admin_init', array(&$this, 'admin_init'), 56);
        }
        add_action('wpcf7_enqueue_scripts', array(&$this, 'scripts'));
    }

    public function version_notice__error()
    {
        $class = 'notice notice-error';
        $message = __('Contact form 7 Repeater requires Contact Form 7 version 4.4 or higher.', 'sample-text-domain');

        printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
    }

    public function admin_init()
    {
        $this->add_tag_generator();
    }

    public function add_tag_generator()
    {
        $tag_generator = WPCF7_TagGenerator::get_instance();
        $tag_generator->add('repeater', __('repeater', WPCF7_REPEATER_TEXT_DOMAIN),
      array(&$this, 'tag_generator'), array( 'nameless' => 1 ));
    }
    public function tag_generator($contact_form, $args = '')
    {
        $args = wp_parse_args($args, array());
        $description = __("Generate a form-tag for a repeater. For more details, see %s.", WPCF7_REPEATER_TEXT_DOMAIN);
        $desc_link = '<a href="https://wordpress.org/plugins/cf7-fields-repeater/">Cf7 Fields Repeater</a>';
        ?>
    <div class="control-box">
    <fieldset>
    <legend><?php echo sprintf(esc_html($description), $desc_link);
        ?></legend>

    <table class="form-table wpcf7-repeater-table">
    <tbody>
      <tr>
      <th scope="row"><label for="<?php echo esc_attr($args['content'] . '-id');
        ?>"><?php echo esc_html(__('Id attribute', WPCF7_REPEATER_TEXT_DOMAIN));
        ?></label></th>
      <td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr($args['content'] . '-id');
        ?>" /></td>
      </tr>
      <tr>
      <th scope="row"><label for="<?php echo esc_attr($args['content'] . '-class');
        ?>"><?php echo esc_html(__('Class attribute', WPCF7_REPEATER_TEXT_DOMAIN));
        ?></label></th>
      <td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr($args['content'] . '-class');
        ?>" /></td>
      </tr>
      <tr>
        <th colspan="2"><h4>Repeater Buttons:</h4></th>
      </tr>
      <tr>
      <th scope="row"><label for="<?php echo esc_attr($args['content'] . '-add');
        ?>"><?php echo esc_html(__('Add button label', WPCF7_REPEATER_TEXT_DOMAIN));
        ?></label></th>
        <td><input type="text" name="add-label" class="addvalue oneline option" id="<?php echo esc_attr($args['content'] . '-add');
        ?>" /><br/>
        <span class="description">
          The label for the add button, Use _ (undescore) instead of (space) since space will break the shortcode,  The _ (undescore) will be replaced with space at the output.
        </span>
        </td>
      </tr>
      <tr>
      <th scope="row"><label for="<?php echo esc_attr($args['content'] . '-remove');
        ?>"><?php echo esc_html(__('Remove button label', WPCF7_REPEATER_TEXT_DOMAIN));
        ?></label></th>
        <td><input type="text" name="remove-label" class="removevalue oneline option" id="<?php echo esc_attr($args['content'] . '-remove');
        ?>" /><br/>
        <span class="description">
         The label for the remove button, Use _ (undescore) instead of (space) since space will break the shortcode,  The _ (undescore) will be replaced with space at the output.
        </span>
        </td>
      </tr>
      <tr>
      <th scope="row"><label for="<?php echo esc_attr($args['content'] . '-show');
        ?>"><?php echo esc_html(__('Base64 Decode', WPCF7_REPEATER_TEXT_DOMAIN));
        ?></label></th>
        <td><input type="checkbox" value="1" name="base64decode" class="base64decodevalue option" id="<?php echo esc_attr($args['content'] . '-base64decode');
        ?>" /><br/>
        <span class="description">
          Text in latin languages like Hebrew, French, Korean, Chinese ... will break the shortcode, To workaround this issue, please encode the labels to Base64 <a href="https://www.base64encode.org/" target="_blank">here</a> and check the Base64 Decode Checkbox, the repeater will decode back the labels to the correct text.
        </span>
        </td>
      </tr>
      <tr>
        <th colspan="2"><h4>Repeater Options:</h4></th>
      </tr>
      <tr>
      <th scope="row"><label for="<?php echo esc_attr($args['content'] . '-index');
        ?>"><?php echo esc_html(__('Index', WPCF7_REPEATER_TEXT_DOMAIN));?><span class="required">*</span></label></th>
        <td><input type="text" name="index" class="indexvalue oneline option" id="<?php echo esc_attr($args['content'] . '-index');
        ?>" /><br/>
        <span class="description">
          The index value is a reference for each repeater, example: repeater with index 0 will show the 1st repeater, index 1 will show the 2nd repeater. etc...
        </span>
        </td>
      </tr>
      <tr>
      <th scope="row"><label for="<?php echo esc_attr($args['content'] . '-min');
        ?>"><?php echo esc_html(__('Minimum', WPCF7_REPEATER_TEXT_DOMAIN));
        ?></label></th>
        <td><input type="text" name="min" class="minvalue oneline option" id="<?php echo esc_attr($args['content'] . '-min');
        ?>" /><br/>
        <span class="description">
          The minimum panels the repeater must create, example: repeater with minimum 0 will have 1 panel as minimum and cannot be removed, minimum 2 will show 2 repeater panels. etc...
        </span>
        </td>
      </tr>
      <tr>
      <th scope="row"><label for="<?php echo esc_attr($args['content'] . '-max');
        ?>"><?php echo esc_html(__('Maximum', WPCF7_REPEATER_TEXT_DOMAIN));
        ?></label></th>
        <td><input type="text" name="max" class="maxvalue oneline option" id="<?php echo esc_attr($args['content'] . '-max');
        ?>" /><br/>
        <span class="description">
          The maximum panels can this repeater create, example: repeater with maximum 2 will have 2 panels as maximum and cannot add more, maximum 3 will limit to 3 repeater panels. etc...
        </span>
        </td>
      </tr>
      <tr>
      <th scope="row"><label for="<?php echo esc_attr($args['content'] . '-show');
        ?>"><?php echo esc_html(__('Initial', WPCF7_REPEATER_TEXT_DOMAIN));
        ?></label></th>
        <td><input type="text" name="show" class="showvalue oneline option" id="<?php echo esc_attr($args['content'] . '-show');
        ?>" /><br/>
        <span class="description">
          The panels to show on startup, should be greater than the minimum panels and less than the maximum panels or else it will be overriden with the minimum and maximum value...
        </span>
        </td>
      </tr>

    </tbody>
    </table>
    </fieldset>
    </div>

    <div class="insert-box">
      <input type="text" name="repeater" class="tag code" readonly="readonly" onfocus="this.select()" />

      <div class="submitbox">
      <input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr(__('Insert Tag', WPCF7_REPEATER_TEXT_DOMAIN));
        ?>" />
      </div>
    </div>
    <?php

    }

    public function save_repeater($contact_form)
    {
        $properties = array();
        if (isset($_POST['wpcf7-repeater']) && sizeof($_POST['wpcf7-repeater']) > 0) {
            $properties['repeater'] = array();
            foreach ($_POST['wpcf7-repeater'] as $index => $r) {
                $properties['repeater'][] = array('text' => trim($r['text']), 'mail' => trim($r['mail']) );
            }
        }
        $properties = array_merge($contact_form->get_properties(), $properties);
        $contact_form->set_properties($properties);
    }
    public function editor_panels($panels)
    {
        $panels['repeater-panel'] = array(
          'title' => __('Repeater', WPCF7_REPEATER_TEXT_DOMAIN),
          'callback' => array(&$this, 'editor_panel')
    );
        return $panels;
    }
    public function editor_panel($post)
    {
        $repeater = (array)$post->prop('repeater');
        ?>
    <h3><?php echo esc_html(__('Repeater', WPCF7_REPEATER_TEXT_DOMAIN));
        ?></h3>

    <?php
    $tag_generator = WPCF7_TagGenerator::get_instance();
        $tag_generator->print_buttons();

        if (sizeof($repeater) == 0) {
            $repeater[0] = array('text' => '');
        }
        ?>
    <div id="wpcf7-repeater-list">
        <script type="text/javascript">
            var _repeater_index = <?php echo sizeof($repeater);
        ?>;
        </script>
      <?php
      foreach ($repeater as $key => $rpt) {
          ?>
      <div class="wpcf7-repeater-item">
          Repeater shortcode:<br/>
        <span class="wpcf7-repeater-shortcode shortcode"><input type="text" readonly="readonly" value="[repeater-<?php echo $key;?>]" /></span>
        <textarea name="wpcf7-repeater[<?php echo $key; ?>][text]" cols="100" rows="10" class="large-text code wpcf7-repeater-textarea"><?php echo esc_textarea($rpt['text']);
          ?></textarea><br/>Repeater email:<br/><small>Customize the repeater email part</small>
        <textarea name="wpcf7-repeater[<?php echo $key; ?>][mail]" cols="100" rows="10" class="large-text code wpcf7-repeater-textarea"><?php echo esc_textarea($rpt['mail']);
          ?></textarea>
        <?php if ($key > 0): ?>
        <a class="button button-secondary wpcf7-repeater-remove" href="#">Remove</a>
        <?php endif; ?>
      </div>
      <?php

      }
        ?>
    </div>
    <script id="wpcf7-repeater-item" type="text/template">
        Repeater shortcode:<br/>
    <span class="wpcf7-repeater-shortcode shortcode"><input type="text" readonly="readonly" value="[repeater-{{index}}]" /></span>
      <textarea name="wpcf7-repeater[{{index}}][text]" cols="100" rows="10" class="large-text code wpcf7-repeater-textarea"></textarea><br/>
     Repeater email:<br/><small>Customize the repeater email part</small>
      <textarea name="wpcf7-repeater[{{index}}][mail]" cols="100" rows="10" class="large-text code wpcf7-repeater-textarea"></textarea>
      <a class="button button-secondary wpcf7-repeater-remove" href="#">Remove</a>
    </script>
<?php if ($this->license->is_licensed()): ?>
    <div class="wpcf7-repeater-actions"><a class="button button-primary wpcf7-repeater-add" href="#">Add More Repeaters</a></div>
<?php else: ?>
    <div class="wpcf7-repeater-unlock"><a href="http://www.ajaxy.org/shop" class="button button-primary">UNLOCK NOW</a>Repeaters are limited to <b>1</b> for the <b>lite</b> version, if you need to create more repeaters please unlock. <br/><i>Cheaper than buying me a Coffee :)</i>
    </div>
      <?php endif;
    }
    public function properties($properties, $WPCF7_ContactForm)
    {
        if (!isset($properties['repeater'])) {
            $properties['repeater'] = array();
        }
        return $properties;
    }
    public function admin_scripts()
    {
        wp_enqueue_script(WPCF7_REPEATER_TEXT_DOMAIN, WPCF7_REPEATER_PLUGIN_URL. '/admin/js/repeater.js', array('wpcf7-admin-taggenerator'), "1.0.0", true);
        wp_enqueue_style(WPCF7_REPEATER_TEXT_DOMAIN."-style", WPCF7_REPEATER_PLUGIN_URL. '/admin/css/styles.css');
    }
    public function scripts()
    {
        $in_footer = true;
        if ('header' === wpcf7_load_js()) {
            $in_footer = false;
        }
        wp_enqueue_script(WPCF7_REPEATER_TEXT_DOMAIN, WPCF7_REPEATER_PLUGIN_URL. '/js/front.js', array( 'contact-form-7' ), "1.0.0", $in_footer);
        wp_enqueue_style(WPCF7_REPEATER_TEXT_DOMAIN."-style", WPCF7_REPEATER_PLUGIN_URL. '/css/styles.css');
    }
    public function shortcode_handler($tag)
    {
        if (! $contact_form = wpcf7_get_current_contact_form()) {
            return '';
        }
        $properties = $contact_form->get_properties();
        if (empty($properties['repeater'])) {
            return "";
        }
        $tag = new WPCF7_Shortcode($tag);

        $class = wpcf7_form_controls_class($tag->type);
        $atts = array();
        $atts['class'] = $tag->get_class_option($class);
        $atts['id'] = $tag->get_id_option();
        $atts['tabindex'] = $tag->get_option('tabindex', 'int', true);
        $index = $tag->get_option('index', 'int', true);
        if (empty($properties['repeater'][$index])) {
            return "";
        }

        $add_label = $tag->get_option('add-label', '', true);
        $add_label = trim($add_label) == "" ? "Add" : str_replace("_", " ", $add_label);

        $remove_label = $tag->get_option('remove-label', '', true);
        $remove_label = trim($remove_label) == "" ? "Remove" : str_replace("_", " ", $remove_label);

        if($tag->has_option( 'base64decode' )){
            $add_label = base64_decode($add_label);
            $remove_label = base64_decode($remove_label);
        }
        $min = $tag->get_option('min', '', true);
        $min = trim($min) == "" ? -1 : intval($min);

        $max = $tag->get_option('max', '', true);
        $max = trim($max) == "" ? -1 : intval($max);



        if ($min > $max && $max != -1) {
            $min = $max;
        }

        $show = $tag->get_option('show', '', true);
        $show = trim($show) == "" ? 0 : intval($show);

        if ($show > $max && $max != -1) {
            $show = $max;
        }

        $value = isset($tag->values[0]) ? $tag->values[0] : '';
        $atts['type'] = 'repeater';
        $id = uniqid();
        $atts['class'] .= " wpcf7-repeater-".$id;
        $atts = wpcf7_format_atts($atts);
        $html = sprintf('<div %1$s>', $atts);
        $replace = wpcf7_do_shortcode($properties['repeater'][$index]['text']);

        $matches = array();
        preg_match_all('/name=(\"(.*?)\")/', $replace, $matches);
        $matches[2] = array_unique($matches[2]);
        foreach ($matches[2] as $match) {
            if (strpos($match, '[]') !== false) {
                $stripped = str_replace('[]', "", $match);
                $replace = str_replace($match, $stripped."-{{repeater}}-{{index}}[]", $replace);
                $replace = str_replace($stripped, $stripped."-{{repeater}}-{{index}}", $replace);
                $replace = str_replace("-{{repeater}}-{{index}}-{{repeater}}-{{index}}", "-{{repeater}}-{{index}}", $replace);
            } else {
                $replace = str_replace($match, $match."-{{repeater}}-{{index}}", $replace);
            }
        }
        $html .= '<script type="text/javascript">if(typeof(wpcf_repeater) == "undefined"){
      var wpcf_repeater = [];
    }
    wpcf_repeater[wpcf_repeater.length] = {min:2, id:"'.$id.'", index:'.$index.', count:0, item:0, min:'.$min.', max:'.$max.', show:'.$show.'};
    </script>';
        $html .= '<div class="wpcf7-repeater-list"></div><div class="wpcf7-repeater-actions"><a class="wpcf7-form-control wpcf7-repeater-add" href="#">'.$add_label.'</a></div>';
        $html .= '<script type="text/template" class="wpcf7-repeater-content">'.$replace.'<div class="wpcf7-repeater-actions"><a class="wpcf7-form-control wpcf7-repeater-remove" href="#">'.$remove_label.'</a></div></script></div>';
        return $html;
    }
    public function validation_filter($result, $_tag)
    {
        $contact_form = wpcf7_get_current_contact_form();
        $properties = $contact_form->get_properties();
        if (empty($properties['repeater'])) {
            return $result;
        }
        foreach ($properties['repeater'] as $index => $repeater) {
            $tags =  WPCF7_ShortcodeManager::get_instance()->scan_shortcode($repeater['text']);
            $indexes = array();
            $repeater_array = (array)$_POST;
            foreach ($repeater_array as $key => $r) {
                foreach ($tags as $tag) {
                    if (strpos($key, $tag['name']) === 0) {
                        $indexes[] = str_replace($tag['name'], "", $key);
                    }
                }
            }
            foreach ($indexes as $n) {
                foreach ($tags as $tag) {
                    $mtag = $tag;
                    $mtag['content'] = isset($repeater_array[$tag['name'].$n]) ? $repeater_array[$tag['name'].$n] : "";
                    $mtag['name'] = $tag['name'].$n;
                    $result = apply_filters('wpcf7_validate_' . $tag['type'], $result, $mtag);
                }
            }
        }
        return $result;
    }
    public function wpcf7_special_mail_tags($_empty, $tagname, $html)
    {
        if (strpos($tagname, 'repeater') === 0) {
            $index = str_replace('repeater', '', str_replace('repeater-', '', $tagname));
            if (trim($index) == "") {
                $index = 0;
            } else {
                $index = intval($index);
            }
            $submission = WPCF7_Submission::get_instance();
            $submitted = $submission ? $submission->get_posted_data() : null;
            if (!$submitted) {
                return "";
            }

            $contact_form = wpcf7_get_current_contact_form();
            $properties = $contact_form->get_properties();
            if (empty($properties['repeater'][$index])) {
                return "";
            }
            $message = $properties['repeater'][$index]['mail'];
            $tags =  WPCF7_ShortcodeManager::get_instance()->scan_shortcode($properties['repeater'][$index]['text']);
            $repeater_array = array();
            $indexes = array();
            $repeater_array = (array)$_POST;
            foreach ($submitted as $key => $r) {
                foreach ($tags as $tag) {
                    if (strpos($key, $tag['name']) === 0) {
                        $indexes[] = str_replace($tag['name'], "", $key);
                    }
                }
            }
            $indexes = array_unique($indexes);
            $mail_msg = "";
            foreach ($indexes as $n) {
                $a = $message;
                foreach ($tags as $tag) {
                    $xo = $submitted[$tag['name'].$n];
                    if(is_array($xo)){
                        $xo = implode(", ", $xo);
                    }
                    $a = str_replace('['.$tag['name']."]", $xo, $a);
                }
                $mail_msg .= $a;
            }
            return $mail_msg;
        }
        return $_empty;
    }
}

global $WPCF7_Repeater;
$WPCF7_Repeater = new WPCF7_Repeater();

?>
