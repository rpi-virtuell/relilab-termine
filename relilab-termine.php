<?php
include_once 'relilab-termine-ics.php';

/**
 * Plugin Name: relilab Termine
 * Plugin URI: https://github.com/rpi-virtuell/relilab-termine
 * Description: Erstellt Termine aus posts
 * Version: 1.2.4
 * Author: Daniel Reintanz
 * Licence: GPLv3
 */
class RelilabTermine
{

    private static string $lastPostMonth = '';

    public function __construct()
    {
        add_shortcode('relilab_termine', array($this, 'termineAusgeben'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('init', array('RelilabTermineICS', 'ical'));
        add_filter('the_content', array($this, 'pushTermineToContent'));
    }

    function pushTermineToContent($content)
    {
        $id = get_the_ID();
        $termineId = get_category_by_slug('termine')->term_id;
        $termList = array_merge([$termineId], get_term_children($termineId, 'category'));
        if (has_term($termList, 'category', $id)) {
            $content = "<p>" . get_post_meta($id, "relilab_startdate", true) . " - " . get_post_meta($id, "relilab_enddate", true) . " <a href='" . !empty(get_post_meta(get_the_ID(), "relilab_custom_zoom_link", true)) ? get_post_meta(get_the_ID(), "relilab_custom_zoom_link", true) : get_option('options_relilab_zoom_link') . "'>Zoom Link</a> </p>$content";
        }
        return $content;
    }


    function termineAusgeben($atts)
    {

        $posts = self::getTerminePostQuery($atts);

        ob_start();
        ?>
        <div class="wp-block-column relilab_termin_header">
            <form id="subCategoryForm" name="subForm" method="get">
                <label for="categorySelector"></label>
                <select class="select" name="category" id="categorySelector">
                    <?php
                    $termineSubCategories = get_categories(
                        array('parent' => get_category_by_slug('termine')->term_id));
                    echo '<option value="termine"> Termine </option>';
                    foreach ($termineSubCategories as $subCategory) {
                        echo '<option value="' . $subCategory->slug . '"'
                            . ($posts['category_name'] == $subCategory->slug ? 'selected' : '') . '>'
                            . $subCategory->name . '</option>';
                    }
                    ?>
                </select>
                <input type="submit" value="Filter">
                <a class="has-text-align-center button"
                   href="<?php echo get_option('options_relilab_kalendertutorial_url'); ?>">
                    📆 <?php echo 'Kalender einbinden' ?></a>
            </form>
        </div>
        <?php

        $posts = get_posts($posts);

        global $post;
        ?>
        <ul>
            <?php
            foreach ($posts as $post) {
                setup_postdata($post);
                if (time() < strtotime(get_post_meta($post->ID, "relilab_enddate")[0]) || $atts['archive'] == 1) {
                    if ($template = locate_template('relilab-Termine'))
                        load_template($template);
                    else
                        load_template(dirname(__FILE__) . '/templates/single-termin-block.php', false);
                }
            }
            ?>
        </ul>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }

    function enqueue_scripts()
    {
        wp_enqueue_style('single-termin-block-style', plugin_dir_url(__FILE__) . 'css/style.css');
    }

    /**
     * @return array
     */
    static public function getTerminePostQuery($atts): array
    {

        $posts = array(
            'post_type' => 'post',
            'posts_per_page' => -1,
            'category_name' => 'termine',
            'meta_key' => 'relilab_startdate',
            'meta_value' => false,
            'meta_compare' => '!=',
            'orderby' => 'meta_value',
            'order' => 'ASC',
        );

        if (isset($atts['category']) && get_category_by_slug($atts['category']))
            $posts['category_name'] = $atts['category'];
        if (isset($_GET['category']) && get_category_by_slug($_GET['category']))
            $posts['category_name'] = $_GET['category'];

        return $posts;
    }

    /**
     * @param string $date
     * @return string
     */
    static function getWochentag(string $date): string
    {
        $wochentag = array(
            'Mon' => 'Montag',
            'Tue' => 'Dienstag',
            'Wed' => 'Mittwoch',
            'Thu' => 'Donnerstag',
            'Fri' => 'Freitag',
            'Sat' => 'Samstag',
            'Sun' => 'Sonntag',
        );
        return $wochentag[date('D', strtotime($date))];
    }

    /**
     * @param string $date
     * @return string
     */
    static function getMonat(string $date): string
    {
        $monat = array(
            'Jan' => 'Januar',
            'Feb' => 'Februar',
            'Mar' => 'März',
            'Apr' => 'April',
            'May' => 'Mai',
            'Jun' => 'Juni',
            'Jul' => 'Juli',
            'Aug' => 'August',
            'Sep' => 'September',
            'Oct' => 'Oktober',
            'Nov' => 'November',
            'Dec' => 'Dezember',
        );
        if (!empty($date))
            return $monat[date('M', strtotime($date))];
        else
            return '';
    }

    static function lastpostmonthcheck(string $currenPostMonth)
    {
        if (self::$lastPostMonth == $currenPostMonth)
            $currenPostMonth = '';
        else
            self::$lastPostMonth = $currenPostMonth;
        return $currenPostMonth;
    }

}

new RelilabTermine();
