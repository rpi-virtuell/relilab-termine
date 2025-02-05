<?php
include_once 'relilab-termine-ics.php';
include_once("admin/event-calendar-admin.php");

/**
 * Plugin Name: Event Termin Kalender
 * Plugin URI: https://github.com/rpi-virtuell/relilab-termine
 * Description: Zeige Posts mit Datumsfeldern als Kalender oder Event Timeline an
 * Version: 3.0.0
 * Author: Daniel Reintanz
 * Licence: GPLv3
 */
class Event_Termin_Calendar
{
    private string $version = '3.0.1';

    private array $available_color_classes = [
        'relimentar',
        'relilab'
    ];

    public function __construct()
    {
        new EventCalendarAdmin();

        add_shortcode('event_calendar', array($this, 'display_termin_calendar'));
        add_shortcode('relilab_termine', array($this, 'display_termin_calendar'));

        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('init', array('RelilabTermineICS', 'ical'));
        //TODO currently the ical logic is going to remain as it is
//        add_filter('the_content', array($this, 'pushTermineToContent'));
    }


    function enqueue_scripts()
    {
        wp_enqueue_style('single-termin-block-style', plugin_dir_url(__FILE__) . 'css/style.css', [], $this->version);
        wp_enqueue_style('event-type-style', plugin_dir_url(__FILE__) . 'css/event_type_style.css', [], $this->version);
        wp_enqueue_script('termin-block-script', plugin_dir_url(__FILE__) . 'js/termin-script.js', array('jquery'), $this->version, true);
    }

    /**
     * @depecated
     * this function isn't used anymore because i think that simply pasting information
     * into the content is a very dirty way to display it still i'd like to find a good alternativ
     *  TODO: find way to display information on detail page of posts
     * @param $content
     * @return mixed|string
     */
    function pushTermineToContent($content)
    {
        $id = get_the_ID();
        $termineId = get_category_by_slug('termine')->term_id;
        $termList = array_merge([$termineId], get_term_children($termineId, 'category'));
        if (has_term($termList, 'category', $id)) {
// This is the old name of the Plugin and this logic is still partially used
            if (!empty(get_post_meta(get_the_ID(), "event_custom_zoom_link", true))) {
                $zoom_link = get_post_meta(get_the_ID(), "event_custom_zoom_link", true);
            } else {
                $zoom_link = get_option("options_event_zoom_link", "");
            }
            $startdate = get_post_meta($id, EventCalendarAdmin::get_termin_start_date_field(), true);
            $enddate = get_post_meta($id, EventCalendarAdmin::get_termin_end_date_field(), true);

            if (is_single() && !empty($startdate) && !empty($enddate)) {
                $content = "<p> Datum : <b>" . date('d.m.Y H:i', strtotime($startdate)) . " - " . date('H:i', strtotime($enddate)) . " </b>   <br>   <a style='font-weight: bold' href='" . $zoom_link . "'>Zoom Link</a> </p>$content";

            }
        }
        return $content;
    }


    /**
     *  This is the core function to display the Calendar or Event List
     * @param array $atts
     *  boolean only_one_month
     *  boolean widgetview  Omits filters and will display only one calendar month by default
     *  date startdate  Set Start date to a different date than today. (Should be readable by strtotime)
     *  boolean listview Displays the events as a list instead of a calendar view
     * @return false|string
     * @throws DateMalformedPeriodStringException
     * @throws DateMalformedStringException
     * @throws coding_exception
     */
    function display_termin_calendar($atts)
    {


        $listView = false;

        $only_one_month = false;
        $widgetview = false;

        if (isset($atts['only_one_month']) && $atts['only_one_month'] === 'on') {
            $only_one_month = true;
        }

        if (isset($atts['widgetview']) && $atts['widgetview'] === 'on') {
            $widgetview = true;
        }

        if (isset($_GET['startdate'])) {
            $startDate = $_GET['startdate'];
        } elseif (isset($atts['startdate'])) {
            $startDate = $atts['startdate'];
        } else {
            $startDate = date('Y-m-d');
        }

        if (isset($atts['listview']) && $atts['listview'] === 'on') {
            $listView = true;
        }

        if (isset($_GET['listview']) && $_GET['listview'] === 'on') {
            $listView = true;
        } elseif (isset($_GET['listview']) && $_GET['listview'] === 'off') {
            $listView = false;
        }

        if (isset($_GET['post-restriction']) && (int)$_GET['post-restriction'] != 0) {
            $postRestriction = (int)$_GET['post-restriction'];
        } elseif (isset($atts['post-restriction']) && (int)$atts['post-restriction'] != 0) {
            $postRestriction = (int)$atts['post-restriction'];
        } else {
            $postRestriction = -1;
        }

        $posts = self::get_event_post_query($atts);

        ob_start();
//        var_dump(get_theme_mod('colorPalette'));

        if (!$widgetview) {
            ?>
            <div class="wp-block-column event_termin_header">
                <form id="subCategoryForm" name="subForm" method="get">
                    <div class="event-filter-container">
                        <div>
                            <label for="categorySelector">
                                Kategorie
                            </label>
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
                        </div>
                        <div>
                            <label for="dateSelector">
                                Startdatum
                            </label>
                            <input type="date" name="startdate" id="dateSelector" value="<?php echo $startDate ?>">
                        </div>
                        <?php if ($listView) {
                            ?>
                            <div>
                                <label for="post-restriction">
                                    Anzahl der Termine
                                </label>
                                <input type="number" name="post-restriction" id="post-restriction"
                                       value="<?php echo $postRestriction == -1 ? '' : $postRestriction; ?>">
                            </div>
                        <?php } ?>
                    </div>
                    <p>
                        Ansicht
                    </p>
                    <div class="event-view-select-container">
                        <label for="listView" class="event-select-label">
                            <input class="event-view-select-input" name="listview" id="listView"
                                   type="radio" value="on" <?php echo $listView ? "checked" : "" ?>>
                            <span title="Listen Ansicht" class="event-view-icon">📃 Listen Ansicht</span>
                        </label>
                        <label for="calendarView" class="event-select-label">
                            <input class="event-view-select-input" name="listview" id="calendarView"
                                   type="radio" value="off" <?php echo !$listView ? "checked" : "" ?>>
                            <span title="Kalender Ansicht" class="event-view-icon">📆 Kalender Ansicht</span>
                        </label>
                    </div>
                    <br>
                    <input class="event-submit-button" type="submit" value="Filter anwenden">
                    <br>
                </form>

                <a class="event-tutorial-link button"
                   href="<?php echo get_option('options_event_kalendertutorial_url', ''); ?>">
                    📆 <?php echo 'Kalender einbinden' ?></a>
            </div>
            <?php

        }

        $posts = get_posts($posts);

        if (!$listView) {

            ?>
            <div class="event-termin-content">
                <?php

                $lastPost = end($posts);

                $datesTillLastPost = new DatePeriod(
                    new DateTime(date("Y-m-d", strtotime($startDate))),
                    new DateInterval('P1D'),
                    new DateTime(get_post_meta($lastPost->ID, EventCalendarAdmin::get_termin_end_date_field(), true))
                );
                $newWeek = true;
                $newMonth = true;

                foreach ($datesTillLastPost

                         as $date) {

                    if ($newMonth) {

                        ?>
                        <div class="event-termin-month">
                        <div class="event-list-month">
                            <h4>
                                <?php
                                $newMonth = false;
                                echo Event_Termin_Calendar::get_monat($date->format(DATE_ATOM)) . ' - ' . $date->format('Y');
                                ?>
                            </h4>
                        </div>
                        <div class="event-termin-month">
                        <div class="event-termin-week-header">
                            <div class="event-termin-Mon non-mobile">
                            <span>
                                Montag
                            </span>
                            </div>
                            <div class="event-termin-Tue non-mobile">
                            <span>
                                Dienstag
                            </span>
                            </div>
                            <div class="event-termin-Wen non-mobile">
                            <span>
                                Mittwoch
                            </span>
                            </div>
                            <div class="event-termin-Thu non-mobile">
                            <span>
                                Donnerstag
                            </span>
                            </div>
                            <div class="event-termin-Fri non-mobile">
                            <span>
                                Freitag
                            </span>
                            </div>
                            <div class="event-termin-Sat non-mobile">
                            <span>
                                Samstag
                            </span>
                            </div>
                            <div class="event-termin-Sun non-mobile">
                            <span>
                                Sonntag
                            </span>
                            </div>

                        </div>
                        <div class="event-termin-week"> <?php
                        $newWeek = false;
                        $whileDate = strtotime('Monday');
                        while (date('D', $whileDate) != $date->format('D')) {
                            ?>
                            <div class="event-termin-spacer event-termin-<?php echo date('D', $whileDate); ?>"></div>  <?php
                            $whileDate = strtotime(date('D', $whileDate) . '+1 days');
                        }
                    }

                if ($newWeek) {
                    ?>
                    <div class="event-termin-week"> <?php
                    $newWeek = false;
                }
                    //Search for post with $data as relilab_startdate

                    $postIds = array_column($posts, EventCalendarAdmin::get_termin_start_date_field(), 'ID');


                    foreach ($postIds as $key => $value) {

                        if (date("Y-m-d", strtotime($value)) != $date->format("Y-m-d"))
                            unset($postIds[$key]);
                    }
                    if (!empty($postIds)) {
                        foreach ($postIds as $postId => $post_date) {
                            $terms = wp_get_post_terms($postId, 'category', array('fields' => 'slugs'));
                            $term_classes = '';
                            foreach ($terms as $term) {
                                $term_classes = $term . '-termin ';
                            }
                            if (empty($term_classes)) {
                                $term_classes = 'default-termin';
                            }
                            //TODO maybe implement check if class is avaialable in event_type_style css
                        }
                        ?>
                        <div class="event-termin-box event-termin-filled <?php echo $term_classes ?> event-termin-<?php echo $date->format('D'); ?>">
                            <div class="event-termin-date">
                                <div class="event-termin-day">
                                    <?php echo $date->format('j') . '. '; ?>
                                </div>
                            </div>
                            <div class="event-termin-details">
                                <div class="event-termin-details-header">
                                    <?php
                                    $timestamp = $date->format(DATE_ATOM);
                                    echo Event_Termin_Calendar::get_wochentag($timestamp) . ' ' . $date->format('j') . '. ' . Event_Termin_Calendar::get_monat($timestamp);
                                    ?>
                                    <?php
                                    $first = true;
                                    foreach ($postIds

                                    as $postId => $termin) {
                                    $terminPost = get_post($postId);
                                    if ($first) {
                                    echo '<br>';
                                    echo date('H:i', strtotime(get_post_meta($postId, EventCalendarAdmin::get_termin_start_date_field(), true))) . ' - ' . date('H:i', strtotime(get_post_meta($postId, EventCalendarAdmin::get_termin_end_date_field(), true)))
                                    ?>
                                </div> <?php
                                $first = false;
                                }
                                else {
                                    ?>
                                    <div class="event-termin-details-header ">
                                        <?php echo date('H:i', strtotime(get_post_meta($postId, EventCalendarAdmin::get_termin_start_date_field(), true))) . ' - ' . date('H:i', strtotime(get_post_meta($postId, EventCalendarAdmin::get_termin_end_date_field(), true))) ?>
                                    </div>
                                    <?php
                                }
                                ?>


                                <div class="event-termin-thumbnail"
                                     style="background-image: url('<?php echo get_the_post_thumbnail_url($postId) ?>')">
                                    <div class="event-termin-post-details">

                                        <h5>
                                            <a class="event-termin-title"
                                               href="<?php echo get_post_permalink($postId) ?>">
                                                <?php echo $terminPost->post_title; ?>
                                            </a>
                                        </h5>
                                        <p>
                                            <?php echo $terminPost->post_excerpt; ?>
                                        </p>
                                        <?php if (time() >= strtotime(get_post_meta($postId, EventCalendarAdmin::get_termin_start_date_field(), true)) && time() <= strtotime(get_post_meta($postId, EventCalendarAdmin::get_termin_end_date_field(), true))) { ?>
                                            <div class="wp-block-group event-meeting-button"
                                                 onclick="location.href='<?php echo !empty(get_post_meta($postId, "relilab_custom_zoom_link", true)) ? get_post_meta($postId, "relilab_custom_zoom_link", true) : get_option('options_relilab_zoom_link') ?>'">
                                                🔴 Zur Live Veranstaltung 🔴
                                            </div>
                                        <?php } else { ?>
                                            <div class="wp-block-group event-meeting-button"
                                                 onclick="location.href='<?php echo get_post_permalink($postId) ?>'">
                                                👉 Mehr zur Veranstaltung 👈
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                        <?php

                    } else {

                        ?>
                        <div class="event-termin-box event-termin-empty event-termin-<?php echo $date->format('D'); ?>">
                            <div class="event-termin-date">
                                <div class="event-termin-day">
                                    <?php echo $date->format('j') . '. '; ?>
                                </div>
                            </div>
                        </div>
                        <?php

                    }
                if ($date->format('D') === 'Sun') {
                    ?> </div> <?php
                    $newWeek = true;
                }

                    if ($date->format('t') === $date->format('d')) {
                        if (!$newWeek) {

                            $whileDate = $date->format('D');
                            while ($whileDate != date('D', strtotime('Monday'))) {
                                ?>
                                <div class="event-termin-spacer event-termin-<?php echo $whileDate; ?>"></div>  <?php
                                $whileDate = date('D', strtotime($whileDate . '+1 days'));
                            }

                            ?> </div> <?php
                            ?> </div> <?php
                        }
                        ?> </div> <?php
                        $newMonth = true;
                        $newWeek = true;
                        if ($only_one_month) {
                            break;
                        }
                    }


                }

                ?>
            </div>
            <?php

        } else {
            $currentMonth = '';
            $numberOfPosts = 0;
            $sameDay = false;
            foreach ($posts as $key => $currentPost) {

                global $post;
                setup_postdata($currentPost);
                $post = $currentPost;
                if (($postRestriction == -1 || $numberOfPosts < $postRestriction) && $startDate <= date('Y-m-d', strtotime(get_post_meta(get_the_ID(), EventCalendarAdmin::get_termin_start_date_field(), true)))) {
                    $numberOfPosts++;

                    if ($currentMonth != Event_Termin_Calendar::get_monat(get_post_meta(get_the_ID(), EventCalendarAdmin::get_termin_start_date_field(), true))) {
                        $currentMonth = Event_Termin_Calendar::get_monat(get_post_meta(get_the_ID(), EventCalendarAdmin::get_termin_start_date_field(), true));
                        ?>
                        <div class="event-list-month">
                            <h3> <?php echo $currentMonth . ' ' . date('Y', strtotime(get_post_meta(get_the_ID(), EventCalendarAdmin::get_termin_start_date_field(), true))) ?> </h3>
                        </div>
                        <?php
                    }

                    if (!$sameDay) {
                        ?>
                        <div class="event-list-termin-box">
                        <?php
                    }
                    ?>
                    <div class="event-termin-details-header">
                        <?php if (!$sameDay) {
                            echo Event_Termin_Calendar::get_wochentag(get_post_meta(get_the_ID(), EventCalendarAdmin::get_termin_start_date_field(), true)) . ' ' .
                                date('j', strtotime(get_post_meta(get_the_ID(), EventCalendarAdmin::get_termin_start_date_field(), true))) . '. ' .
                                Event_Termin_Calendar::get_monat(get_post_meta(get_the_ID(), EventCalendarAdmin::get_termin_start_date_field(), true)); ?>
                            <br>
                        <?php } ?>
                        <?php echo date('H:i', strtotime(get_post_meta(get_the_ID(), EventCalendarAdmin::get_termin_start_date_field(), true))) . ' - ' . date('H:i', strtotime(get_post_meta(get_the_ID(), EventCalendarAdmin::get_termin_end_date_field(), true))) ?>
                    </div>
                    <div class="event-termin-content">
                        <div class="event-termin-thumbnail"
                             style="background-image: url('<?php echo get_the_post_thumbnail_url(get_the_ID()) ?>')">
                            <div class="event-termin-post-details">
                                <h4 class="event-termin-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h4>
                                <p class="event-termin-excerpt"><?php echo get_the_excerpt(); ?></p>
                                <?php if (time() >= strtotime(get_post_meta(get_the_ID(), EventCalendarAdmin::get_termin_start_date_field(), true)) && time() <= strtotime(get_post_meta(get_the_ID(), EventCalendarAdmin::get_termin_end_date_field(), true))) { ?>
                                    <div class="wp-block-group event-meeting-button"
                                         onclick="location.href='<?php echo !empty(get_post_meta(get_the_ID(), "relilab_custom_zoom_link", true)) ? get_post_meta(get_the_ID(), "relilab_custom_zoom_link", true) : get_option('options_relilab_zoom_link') ?>'">
                                        🔴 Zur Live Veranstaltung 🔴
                                    </div>
                                <?php } else { ?>
                                    <div class="wp-block-group event-meeting-button"
                                         onclick="location.href='<?php the_permalink(); ?>'">
                                        👉 Mehr zur Veranstaltung 👈
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    if (isset($posts[$key + 1]) && date('Y-m-d', strtotime(get_post_meta($posts[$key + 1]->ID, EventCalendarAdmin::get_termin_start_date_field(), true)))
                        != date('Y-m-d', strtotime(get_post_meta(get_the_ID(), EventCalendarAdmin::get_termin_start_date_field(), true)))) {
                        ?> </div> <?php
                        $sameDay = false;
                    } else {
                        $sameDay = true;
                    }
                }
            }
            wp_reset_postdata();
        }

        return ob_get_clean();
    }

    /**
     * @return array
     */
    static public function get_event_post_query($atts): array
    {


        $posts = array(
            'post_type' => EventCalendarAdmin::get_termin_selected_post_type(),
            'posts_per_page' => -1,
            'meta_key' => EventCalendarAdmin::get_termin_start_date_field(),
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
    static function get_monat(string $date): string
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

    /**
     * @param string $date
     * @return string
     */
    static function get_wochentag(string $date): string
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

}

new Event_Termin_Calendar();
