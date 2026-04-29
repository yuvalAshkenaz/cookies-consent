<?php
/*
Plugin Name: dooble cookies consent
Description: An accessible cookies consent plugin with customizable message and buttons text.
Version: 1.6
Author: dooble
*/

add_action('wp_enqueue_scripts', 'dooble_cookie_consent_enqueue_scripts');
add_action('init', 'dooble_cookie_consent_init');

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
	
	// קביעת השפה הכללית של האתר
    $site_locale = get_locale();
    $current_lang = substr($site_locale, 0, 2); // מחזיר 'he', 'en' וכו'

    // בדיקה שהשפה קיימת במערך הדיפולטים שלנו, אם לא - קבע עברית
    $supported_langs = ['he', 'en', 'ar'];
    if ( ! in_array( $current_lang, $supported_langs ) ) {
        $current_lang = 'he';
    }
	
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
	
    // שימוש בטכניקה הבטוחה לבדיקת השפה כפי שדיברנו קודם
	if (defined('ICL_LANGUAGE_CODE')) {
		$current_lang = ICL_LANGUAGE_CODE;
	} elseif (isset($GLOBALS['ICL_LANGUAGE_CODE'])) {
		$current_lang = $GLOBALS['ICL_LANGUAGE_CODE'];
	} else {
		$current_lang = 'he';
	}
	
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
				// השדה החדש של התמונה
				array(
					'key' => 'field_cookie_img',
					'label' => 'תמונה',
					'name' => 'cookie_img',
					'type' => 'image',
					'return_format' => 'id',
					'preview_size' => 'thumbnail',
					'library' => 'all',
					'wrapper' => array(
						'width' => 20,
					),
				),
                array(
                    'key' => 'field_cookie_message',
                    'label' => 'הודעת Cookies',
                    'name' => 'cookie_message',
                    'type' => 'wysiwyg',
                    'default_value' => $default_values['message'][ $current_lang ],
					'wrapper' => array(
						'width' => 80,
					),
                ),
                array(
                    'key' => 'field_cookie_accept_btn_text',
                    'label' => 'טקסט בכפתור אישור',
                    'name' => 'cookie_accept_btn_text',
                    'type' => 'text',
                    'default_value' => $default_values['accept'][ $current_lang ],
					'wrapper' => array(
						'width' => 33,
					),
                ),
                array(
                    'key' => 'field_cookie_decline_btn_text',
                    'label' => 'טקסט בכפתור לא מאשר',
                    'name' => 'cookie_decline_btn_text',
                    'type' => 'text',
                    'default_value' => $default_values['decline'][ $current_lang ],
					'wrapper' => array(
						'width' => 33,
					),
                ),
                // השדה החדש שהוספנו להסתרת כפתור הסירוב
                array(
                    'key' => 'field_cookie_hide_decline_btn',
                    'label' => 'הסתר כפתור סירוב',
                    'name' => 'cookie_hide_decline_btn',
                    'type' => 'true_false',
                    'message' => 'אל תציג כפתור סירוב',
                    'default_value' => 0,
                    'ui' => 1,
                    'wrapper' => array(
                        'width' => 33,
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
		
		// 1. קודם שולפים את הסקריפטים מההגדרות
		$cookie_scripts_after_approve = get_field('cookie_scripts_after_approve', 'option');
		
		// 2. עכשיו מדפיסים את הסקריפט עם המשתנה שכבר מוגדר
		echo '<script>
		function enableNonEssentialScripts() {' .
			$cookie_scripts_after_approve . '
		}
		</script>';
		
		// 3. תנאי יציאה מוקדמת
		if ( isset( $_COOKIE['od_consent'] ) || ( ! empty( $cookie_active ) && ! $cookie_active ) ) {
			return;
		}
		if( ! empty( $cookie_admin_active ) && $cookie_admin_active && ! current_user_can( 'administrator' ) ) {
			return;
		}
		
		// 4. שאר משתני ה-ACF (את הסקריפטים כבר שאבנו למעלה)
		$cookie_img = get_field('cookie_img', 'option');
		$cookie_message = get_field('cookie_message', 'option');
		$accept_btn_text = get_field('cookie_accept_btn_text', 'option');
		$decline_btn_text = get_field('cookie_decline_btn_text', 'option');
        $hide_decline_btn = get_field('cookie_hide_decline_btn', 'option');
		
		// 5. ערכי ברירת מחדל אם אין ערכים מוזנים
		if ( empty( $cookie_message ) ) {
			$cookie_message = $default_values['message'][ $current_lang ];
		}
		if ( empty( $accept_btn_text ) ) {
			$accept_btn_text = $default_values['accept'][ $current_lang ];
		}
		if ( empty( $decline_btn_text ) ) {
			$decline_btn_text = $default_values['decline'][ $current_lang ];
		}

        // 6. הכנת ה-HTML של התמונה ושל כפתור הסירוב
		$cookie_img_id = get_field('cookie_img', 'option');
        $cookie_img_html = '';

        if ( ! empty( $cookie_img_id ) ) {
			$cookie_img_html = wp_get_attachment_image( 
				$cookie_img_id, 
				'full', 
				false, 
				array(
					'class'	=> 'cookie-img',
				) 
			);
        }
		
        $decline_btn_html = '';
        if ( ! $hide_decline_btn ) {
            $decline_btn_html = '
            <button type="button" id="od-decline" class="consent-decline" aria-controls="cookie-banner"> 
                ' . $decline_btn_text . '
            </button>';
        }

		// 7. הדפסת הבאנר עצמו (התמונה מודפסת ראשונה)
		echo '
		<div id="cookie-banner" class="consent" role="region" aria-label="' . __('Cookie consent', 'dooble_cookies_consent') . '">
			' . $cookie_img_html . '
			<div class="consent-inner">
				<div class="consent-content">
					' . $cookie_message . '
				</div>
				<div class="consent-btns">
                    ' . $decline_btn_html . '
					<button type="button" id="od-accept" class="consent-accept" aria-controls="cookie-banner"> 
						' . $accept_btn_text . '
					</button>
				</div>
			</div>
		</div>
		';
	}
}