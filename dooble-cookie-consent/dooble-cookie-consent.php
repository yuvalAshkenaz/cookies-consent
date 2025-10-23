<?php
/*
Plugin Name: dooble cookies consent
Description: An accessible cookies consent plugin with customizable message and buttons text.
Version: 1.2
Author: dooble
*/

add_action('wp_enqueue_scripts', 'dooble_cookie_consent_enqueue_scripts');
add_action('plugins_loaded', 'dooble_cookie_consent_init');

/*
* Add css file to admin
*/
add_action('admin_head', 'add_admin_style');
function add_admin_style() {
	wp_enqueue_style('admin-style', plugin_dir_url(__FILE__) . 'assets/admin-style.css', array(), null, 'all');
}

// Register and enqueue the script and styles
function dooble_cookie_consent_enqueue_scripts() {
    wp_enqueue_script('cookie-consent', plugin_dir_url(__FILE__) . 'assets/cookie-consent.js', array(), null, true);
    wp_enqueue_style('cookie-consent', plugin_dir_url(__FILE__) . 'assets/cookie-consent.css', array(), null, 'all');
}

// Hook into WordPress initialization to ensure ACF is loaded first
function dooble_cookie_consent_init() {
	global $default_values, $current_lang;
	
    // Check if ACF plugin is active
    if ( function_exists('acf_add_options_page') ) {
        // Create ACF options page
        acf_add_options_page(array(
            'page_title'    => 'הגדרות הסכמה לקובצי Cookies',
            'menu_title'    => 'הסכמה לקובצי Cookies',
            'menu_slug'     => 'cookie-consent-settings',
            'capability'    => 'edit_posts',
            'redirect'      => false
        ));
    } else {
        error_log('ACF לא פעיל!');
    }
	
	$default_values = array(
		'message' => array(
			'he' => 'אנו משתמשים בקובצי Cookie לתפקודים חיוניים, ניתוח נתונים ושיווק. באפשרותך לקבל או לדחות קובצי Cookie שאינם חיוניים.',
			'en' => 'We use cookies for essential functions, analytics and marketing. You can accept or decline non-essential cookies.',
			'ar' => 'نستخدم ملفات تعريف الارتباط للوظائف الأساسية والتحليلات والتسويق. يمكنك قبول أو رفض ملفات تعريف الارتباط غير الأساسية.',
		),
		'accept' => array(
			'he' => 'מסכים',
			'en' => 'Accept',
			'ar' => 'يقبل',
		),
		'decline' => array(
			'he' => 'לא מסכים',
			'en' => 'Decline',
			'ar' => 'انخفاض',
		),
	);
	$current_lang = ICL_LANGUAGE_CODE;
	
    // Create the ACF fields for cookie consent settings
    if( function_exists('acf_add_local_field_group') ) {
        acf_add_local_field_group(array(
            'key' => 'group_cookie_consent',
            'title' => 'הגדרות הסכמה לקובצי Cookies',
            'fields' => array(
				array(
					'key' => 'field_cookie_time',
					'label' => 'אחרי כמה ימים להציג שוב את ההודעה?',
					'name' => 'cookie_time',
					'type' => 'number',
					'step' => 1,
					'default_value' => 90,
				),
                array(
                    'key' => 'field_cookie_message',
                    'label' => 'הודעת Cookies',
                    'name' => 'cookie_message',
                    'type' => 'wysiwyg',
                    'default_value' => $default_values['message'][ $current_lang ],
                ),
                array(
                    'key' => 'field_cookie_accept_btn_text',
                    'label' => 'טקסט בכפתור אישור',
                    'name' => 'cookie_accept_btn_text',
                    'type' => 'text',
                    'default_value' => $default_values['accept'][ $current_lang ],
					'wrapper' => array(
						'width' => 50,
					),
                ),
                array(
                    'key' => 'field_cookie_decline_btn_text',
                    'label' => 'טקסט בכפתור לא מאשר',
                    'name' => 'cookie_decline_btn_text',
                    'type' => 'text',
                    'default_value' => $default_values['decline'][ $current_lang ],
					'wrapper' => array(
						'width' => 50,
					),
                ),
				array(
					'key' => 'field_cookie_active',
					'label' => 'פעיל',
					'name' => 'cookie_active',
					'type' => 'true_false',
					'message' => 'פעיל',
					'default_value' => 1,
					'ui' => 1,
				),
				array(
					'key' => 'field_cookie_admin_active',
					'label' => 'מוצג למנהל האתר בלבד',
					'name' => 'cookie_admin_active',
					'type' => 'true_false',
					'message' => 'מוצג למנהל האתר בלבד',
					'ui' => 1,
				),
				array(
					'key' => 'field_cookie_scripts_after_approve',
					'label' => 'סקריפטים שיופעלו לאחר אישור הגולש',
					'instructions' => 'ללא HTML',
					'name' => 'cookie_scripts_after_approve',
					'type' => 'textarea',
					'rows' => 22,
				),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => 'cookie-consent-settings',
                    ),
                ),
            ),
        ));
    }
	
	// Display the cookie consent in the site
	add_action('wp_footer', 'dooble_cookie_consent_banner');
	function dooble_cookie_consent_banner() {
		global $default_values, $current_lang;
		
		$cookie_active = get_field('cookie_active', 'option');
		$cookie_admin_active = get_field('cookie_admin_active', 'option');
		
		if ( isset( $_COOKIE['od_consent'] ) || ( ! empty( $cookie_active ) && ! $cookie_active ) ) {
			return;
		}
		if( ! empty( $cookie_admin_active ) && $cookie_admin_active && ! current_user_can( 'administrator' ) ) {
			return;
		}
		
		$cookie_message = get_field('cookie_message', 'option');
		$accept_btn_text = get_field('cookie_accept_btn_text', 'option');
		$decline_btn_text = get_field('cookie_decline_btn_text', 'option');
		$cookie_scripts_after_approve = get_field('cookie_scripts_after_approve', 'option');
		
		if ( empty( $cookie_message ) ) {
			$cookie_message = $default_values['message'][ $current_lang ];
		}
		if ( empty( $accept_btn_text ) ) {
			$accept_btn_text = $default_values['accept'][ $current_lang ];
		}
		if ( empty( $decline_btn_text ) ) {
			$decline_btn_text = $default_values['decline'][ $current_lang ];
		}

		echo '
		<div id="cookie-banner" class="consent" role="region" aria-label="' . __('Cookie consent', 'dooble_cookies_consent') . '">
			<div class="consent-inner">
				<div class="consent-content">
					' . $cookie_message . '
				</div>
				<div class="consent-btns">
					<button type="button" id="od-decline" class="consent-decline" aria-label="' . __('Decline cookies', 'dooble_cookies_consent') . '" aria-controls="cookie-banner"> 
						' . $decline_btn_text . '
					</button>
					<button type="button" id="od-accept" class="consent-accept" aria-label="' . __('Accept cookies', 'dooble_cookies_consent') . '" aria-controls="cookie-banner"> 
						' . $accept_btn_text . '
					</button>
				</div>
			</div>
		</div>
		<script>
		function enableNonEssentialScripts() {' .
			
			// כאן אפשר להוסיף סקריפטים אחרים לא-הכרחיים

			// iframe-אם טוענים טאג מנגר אז לא צריך לטעון את ה
			
			// קריאה לקובץ סקריפט
			// const s = document.createElement("script");
			// s.src = "https://www.test.com";
			// s.type = "text/javascript";
			// s.async = false;
			// document.body.appendChild(s);
			
			$cookie_scripts_after_approve . '
		}
		</script>';
	}
}