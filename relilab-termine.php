<?php
include_once 'relilab-termine-ics.php';

/**
 * Plugin Name: relilab Termine
 * Plugin URI: https://github.com/rpi-virtuell/relilab-termine
 * Description: Erstellt Termine aus posts
 * Version: 1.0
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
    }

    function termineAusgeben($atts)
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

        if (isset($_GET['cat']) && get_category_by_slug($_GET['cat']))
            $posts['category_name'] = $_GET['cat'];

        ob_start();
        ?>
        <div class="wp-block-column">
            <a class="has-text-align-center button <?php echo $_GET['cat'] == NULL ? 'active' : '' ?>"
               href="<?php echo get_permalink(); ?>"><?php echo 'Alle Termine' ?></a>
            <a class="has-text-align-center button <?php echo $_GET['cat'] == 'relilab-talks' ? 'active' : '' ?>"
               href="<?php echo '?cat=relilab-talks'; ?>"><?php echo 'relilab-Talks' ?></a>
            <a class="has-text-align-center button <?php echo $_GET['cat'] == 'relilab-cafe' ? 'active' : '' ?>"
               href="<?php echo '?cat=relilab-cafe'; ?>"><?php echo 'relilab-CAFÉ' ?></a>
            <a class="has-text-align-center button <?php echo $_GET['cat'] == 'relilab-impuls' ? 'active' : '' ?>"
               href="<?php echo '?cat=relilab-impuls'; ?>"><?php echo 'relilab-Impuls' ?></a>
            <a class="has-text-align-center button" href="<?php get_option('relilab_kalendertutorial_url'); ?>">
                📆 <?php echo 'ICS Datei herunterladen' ?></a>
        </div>
        <?php

        $posts = get_posts($posts);

        global $post;
        ?>
        <ul>
            <?php
            foreach ($posts as $post) {
                setup_postdata($post);
                {
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
