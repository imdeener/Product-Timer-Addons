<?php
/*
Plugin Name: Product Timer Addons for WooCommerce
Description: ปลั๊กอินนี้เป็นส่วนเสริมของ WooCommerce ที่จะเพิ่มฟังก์ชั่นการตั้งเวลาสินค้า
Version: 1.0
Author: Epik Web
*/

// เพิ่มฟังก์ชันที่จะแสดงเนื้อหาของหน้าตั้งค่า

function woopt_addons_settings_page()
{
?>
    <div class="wrap">
        <h1>Product Timer Addons Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields("woopt-addons-settings-group");
            do_settings_sections("woopt-addons-settings-group");
            submit_button();
            ?>
        </form>
    </div>
<?php
}

function add_woopt_addons_menu()
{
    add_menu_page(
        "Product Timer Addons", // ชื่อหน้า
        "Product Timer Addons", // ชื่อเมนู
        "manage_options", // สิทธิ์การเข้าถึง
        "woopt-addons-settings", // slug ของเมนู
        "woopt_addons_settings_page", // ฟังก์ชันที่เรียกเมื่อคลิกเมนู
        null, // ไอคอน
        100 // ตำแหน่งเมนู
    );
}
add_action("admin_menu", "add_woopt_addons_menu");

function woopt_addons_settings()
{
    register_setting("woopt-addons-settings-group", "woopt_enable_countdown");
    register_setting("woopt-addons-settings-group", "woopt_enable_discount_percentage");
    register_setting("woopt-addons-settings-group", "woopt_enable_bricks_tag");

    add_settings_section("woopt-addons-main-section", null, null, "woopt-addons-settings-group");

    add_settings_field("woopt-enable-countdown", "เปิดใช้ Product Countdown <p class='description'>Shortcode: [woopt_countdown]</p>", "woopt_enable_countdown_display", "woopt-addons-settings-group", "woopt-addons-main-section");
    add_settings_field("woopt-enable-discount-percentage", "เปิดใช้ Product Discount Percentage <p class='description'>Shortcode: [discount_percentage]</p>", "woopt_enable_discount_percentage_display", "woopt-addons-settings-group", "woopt-addons-main-section");
    add_settings_field("woopt-enable-bricks-tag", "เปิดใช้ Bricks Builder Tag <p class='description'>เพิ่ม Dynamic Data Tag ใน Bricks Builder</p>", "woopt_enable_bricks_tag_display", "woopt-addons-settings-group", "woopt-addons-main-section");
}

function woopt_enable_countdown_display()
{
?>
    <input type="checkbox" name="woopt_enable_countdown" value="1" <?php checked(1, get_option('woopt_enable_countdown'), true); ?> />
<?php
}

function woopt_enable_discount_percentage_display()
{
?>
    <input type="checkbox" name="woopt_enable_discount_percentage" value="1" <?php checked(1, get_option('woopt_enable_discount_percentage'), true); ?> />
<?php
}

function woopt_enable_bricks_tag_display()
{
?>
    <input type="checkbox" name="woopt_enable_bricks_tag" value="1" <?php checked(1, get_option('woopt_enable_bricks_tag'), true); ?> />
<?php
}

add_action("admin_init", "woopt_addons_settings");

//Hook ผ่าน save_post action: สามารถเพิ่ม action ที่จะถูกเรียกเมื่อมีการเซฟ post:

add_action('save_post', 'update_woopt_end_time', 10, 2);

function update_woopt_end_time($post_id, $post)
{
    // ตรวจสอบเพื่อไม่ให้รันหลายครั้งเนื่องจาก save_post ถูกเรียกหลายครั้ง
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    $woopt_actions = get_post_meta($post_id, 'woopt_actions', true);

    $data = maybe_unserialize($woopt_actions);
    $end_date = '';

    if ($data) {
        foreach ($data as $mainKey => $mainValue) {
            if (isset($mainValue['timer'])) {
                foreach ($mainValue['timer'] as $timerKey => $timerValue) {
                    if (isset($timerValue['val'])) {
                        $acf_date = $timerValue['val'];
                        $date = DateTime::createFromFormat('m/d/Y h:i a', $acf_date);
                        if ($date !== false) { // ตรวจสอบว่าวันที่สามารถสร้างได้
                            $end_date = $date->format('m/d/Y H:i'); // ที่นี่เปลี่ยน format เป็น H:i
                            break;
                        }
                    }
                }
                if ($end_date) break;
            }
        }
    }

    if ($end_date) {
        update_post_meta($post_id, 'woopt_end_time', $end_date);
    } else {
        // ถ้าไม่มี end_date, ลบ meta_key ที่ชื่อว่า woopt_end_time
        delete_post_meta($post_id, 'woopt_end_time');
    }
}


//Shortcode: Show Countdown => [woopt_countdown]

function woopt_countdown_shortcode($atts)
{
    if (get_option('woopt_enable_countdown') != 1) {
        return '';
    }

    $post_id = get_the_ID();
    $end_date_str = get_post_meta($post_id, 'woopt_end_time', true);
    $end_date = new DateTime($end_date_str);
    $now = new DateTime();

    $diff = $end_date->diff($now);

    $days = $diff->d;
    $hours = $diff->h;
    $minutes = $diff->i;
    $seconds = $diff->s;

    $initial_display = "";

    if ($days > 0) {
        $initial_display .= "{$days}d : ";
    }
    if ($days > 0 || $hours > 0) {
        $initial_display .= "{$hours}h : ";
    }
    if ($days > 0 || $hours > 0 || $minutes > 0) {
        $initial_display .= "{$minutes}m : ";
    }
    $initial_display .= "{$seconds}s";

    if ($end_date < $now) {
        $initial_display = "EXPIRED";
    }

    return "<div class='woopt-countdown' data-enddate='{$end_date_str}'>{$initial_display}</div>";
}
add_shortcode('woopt_countdown', 'woopt_countdown_shortcode');

// Shortcode: Show Discount Percentage => [discount_percentage]
function wc_discount_percentage_shortcode($atts)
{
    if (get_option('woopt_enable_discount_percentage') != 1) {
        return ''; // หากการตั้งค่าถูกปิดไว้ จะไม่แสดง shortcode
    }

    $atts = shortcode_atts(array(
        'id' => null,
    ), $atts, 'discount_percentage');

    if (null === $atts['id']) {
        global $product;
    } else {
        $product = wc_get_product($atts['id']);
    }

    // ตรวจสอบถ้า $product ไม่มีค่า
    if (!$product) return '';

    if ($product->is_on_sale()) {
        $regular_price = $product->get_regular_price();
        $sale_price = $product->get_sale_price();

        if ($regular_price && $sale_price) {
            $percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
            return 'Save -' . $percentage . '%';
        }
    }

    return '';
}
add_shortcode('discount_percentage', 'wc_discount_percentage_shortcode');

// เพิ่ม tag ใหม่ใน Bricks Builder

add_filter('bricks/dynamic_tags_list', 'add_woopt_endtime_to_builder');
function add_woopt_endtime_to_builder($tags)
{
    if (get_option('woopt_enable_bricks_tag') == 1) {
        $tags[] = [
            'name'  => '{woopt_end_time}',
            'label' => 'WooPT End Time',
            'group' => 'WooCommerce',
        ];
    }

    return $tags;
}

add_filter('bricks/dynamic_data/render_tag', 'get_woopt_endtime_value', 10, 3);
function get_woopt_endtime_value($tag, $post, $context = 'text')
{
    if ($tag !== 'woopt_end_time') {
        return $tag;
    }

    // ทำการดึงค่าเวลาสิ้นสุดจาก metadata
    $post_id = $post->ID;
    $woopt_actions = get_post_meta($post_id, 'woopt_actions', true);
    $data = maybe_unserialize($woopt_actions);

    $end_date = '';
    if ($data) {
        foreach ($data as $mainValue) {
            if (isset($mainValue['timer'])) {
                foreach ($mainValue['timer'] as $timerValue) {
                    if (isset($timerValue['val'])) {
                        $dateTime = DateTime::createFromFormat('m/d/Y h:i a', $timerValue['val']);
                        if ($dateTime === false) {
                            // Handle error, for example:
                            error_log('Invalid date format: ' . $timerValue['val']);
                            return; // or provide a default value or take other error-handling steps
                        }
                        $end_date = $dateTime->format('m/d/Y H:i');
                    }
                }
            }
        }
    }

    return $end_date;
}

add_filter('bricks/dynamic_data/render_content', 'render_woopt_endtime_tag', 10, 3);
add_filter('bricks/frontend/render_data', 'render_woopt_endtime_tag', 10, 2);
function render_woopt_endtime_tag($content, $post, $context = 'text')
{
    if (strpos($content, '{woopt_end_time}') === false) {
        return $content;
    }

    $end_time_value = get_woopt_endtime_value('woopt_end_time', $post, $context);
    $content = str_replace('{woopt_end_time}', $end_time_value, $content);

    return $content;
}


// JavaScript

function enqueue_woopt_addons_scripts()
{
    wp_enqueue_script('woopt-addons', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'enqueue_woopt_addons_scripts');
