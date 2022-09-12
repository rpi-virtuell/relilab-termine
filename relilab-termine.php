<?php
include_once 'relilab-termine-ics.php';

/**
 * Plugin Name: relilab Termine
 * Plugin URI: https://github.com/rpi-virtuell/relilab-termine
 * Description: Erstellt Termine aus posts
 * Version: 2.2.0
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
            if (!empty(get_post_meta(get_the_ID(), "relilab_custom_zoom_link", true))) {
                $zoom_link = get_post_meta(get_the_ID(), "relilab_custom_zoom_link", true);
            } else {
                $zoom_link = get_option("options_relilab_zoom_link");
            }
            $content = "<p>" . get_post_meta($id, "relilab_startdate", true) . " - " . get_post_meta($id, "relilab_enddate", true) . " <a href='" . $zoom_link . "'>Zoom Link</a> </p>$content";
        }
        return $content;
    }


    function termineAusgeben($atts)
    {
        $listView = false;

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
        } elseif (isset($_GET['calendarview']) && $_GET['calendarview'] === 'on') {
            $listView = false;
        }

        if (isset($_GET['post-restriction']) && (int)$_GET['post-restriction'] != 0) {
            $postRestriction = (int)$_GET['post-restriction'];
        } elseif (isset($atts['post-restriction']) && (int)$atts['post-restriction'] != 0) {
            $postRestriction = (int)$atts['post-restriction'];
        } else {
            $postRestriction = -1;
        }

        $posts = self::getTerminePostQuery($atts);

        ob_start();
        ?>
        <div class="wp-block-column relilab_termin_header">
            <form id="subCategoryForm" name="subForm" method="get">
                <div class="relilab-filter-container">
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
                <div class="relilab-view-slider-container">
                    <?php if (isset($atts['listview']) && $atts['listview'] === 'on') { ?>
                        <div title="Listen Ansicht" class="relilab-view-icon">📃 Listen Ansicht</div>
                        <label for="viewSelector" class="relilab-slider-label">
                            <input class="relilab-view-slider-input" name="calendarview" id="viewSelector"
                                   type="checkbox" <?php echo !$listView ? "checked" : "" ?>>
                            <span class="relilab-slider"></span>
                        </label>
                        <div title="Kalender Ansicht" class="relilab-view-icon">📆 Kalender Ansicht</div>
                    <?php } else { ?>
                        <div title="Kalender Ansicht" class="relilab-view-icon">📆 Kalender Ansicht</div>
                        <label for="viewSelector" class="relilab-slider-label">
                            <input class="relilab-view-slider-input" name="listview" id="viewSelector"
                                   type="checkbox" <?php echo $listView ? "checked" : "" ?>>
                            <span class="relilab-slider"></span>
                        </label>
                        <div title="Listen Ansicht" class="relilab-view-icon">📃 Kalender Ansicht</div>
                    <?php } ?>
                </div>
                <br>
                <input class="relilab-submit-button" type="submit" value="Filter anwenden">
                <br>
            </form>

            <a class="relilab-tutorial-link button"
               href="<?php echo get_option('options_relilab_kalendertutorial_url'); ?>">
                📆 <?php echo 'Kalender einbinden' ?></a>
        </div>
        <?php

        $posts = get_posts($posts);

        if (!$listView) {

            ?>
            <div class="relilab-termin-content">
                <?php

                $lastPost = end($posts);

                $datesTillLastPost = new DatePeriod(
                    new DateTime(date("Y-m-d", strtotime($startDate))),
                    new DateInterval('P1D'),
                    new DateTime(get_post_meta($lastPost->ID, 'relilab_startdate', true))
                );
                $newWeek = true;
                $newMonth = true;

                foreach ($datesTillLastPost

                         as $date) {

                    if ($newMonth) {

                        ?>
                        <div class="relilab-termin-month">
                        <h4>
                            <?php
                            $newMonth = false;
                            echo RelilabTermine::getMonat($date->format(DATE_ATOM)) . ' - ' . $date->format('Y');
                            ?>
                        </h4>
                        <div class="relilab-termin-month">
                        <div class="relilab-termin-week-header">
                            <div class="relilab-termin-Mon non-mobile">
                            <span>
                                Montag
                            </span>
                            </div>
                            <div class="relilab-termin-Tue non-mobile">
                            <span>
                                Dienstag
                            </span>
                            </div>
                            <div class="relilab-termin-Wen non-mobile">
                            <span>
                                Mittwoch
                            </span>
                            </div>
                            <div class="relilab-termin-Thu non-mobile">
                            <span>
                                Donnerstag
                            </span>
                            </div>
                            <div class="relilab-termin-Fri non-mobile">
                            <span>
                                Freitag
                            </span>
                            </div>
                            <div class="relilab-termin-Sat non-mobile">
                            <span>
                                Samstag
                            </span>
                            </div>
                            <div class="relilab-termin-Sun non-mobile">
                            <span>
                                Sonntag
                            </span>
                            </div>

                        </div>

                        <?php
                        // TODO: The relilab-termin-Mon etc. divs need to have the same width
                        ?>

                        <div class="relilab-termin-week"> <?php
                        $newWeek = false;
                        $whileDate = strtotime('Monday');
                        while (date('D', $whileDate) != $date->format('D')) {
                            ?>
                            <div class="relilab-termin-spacer relilab-termin-<?php echo date('D', $whileDate); ?>"></div>  <?php
                            $whileDate = strtotime(date('D', $whileDate) . '+1 days');
                        }
                    }

                if ($newWeek) {
                    ?>
                    <div class="relilab-termin-week"> <?php
                    $newWeek = false;
                }
                    //Search for post with $data as relilab_startdate

                    $postIds = array_column($posts, 'relilab_startdate', 'ID');

                    foreach ($postIds as $key => $value) {
                        if (date("Y-m-d", strtotime($value)) != $date->format("Y-m-d"))
                            unset($postIds[$key]);
                    }
                    if (!empty($postIds)) {


                        ?>
                        <div class="relilab-termin-box relilab-termin-filled relilab-termin-<?php echo $date->format('D'); ?>">
                            <div class="relilab-termin-date">
                                <div class="relilab-termin-day">
                                    <?php echo $date->format('j') . '. '; ?>
                                </div>
                            </div>
                            <div class="relilab-termin-details">
                                <div class="relilab-termin-details-header">
                                    <?php
                                    $timestamp = $date->format(DATE_ATOM);
                                    echo RelilabTermine::getWochentag($timestamp) . ' ' . $date->format('j') . '. ' . RelilabTermine::getMonat($timestamp);
                                    ?>
                                </div>
                                <?php
                                foreach ($postIds

                                         as $postId => $termin) {
                                    $terminPost = get_post($postId);
                                    ?>
                                    <div class="relilab-termin-thumbnail"
                                         style="background-image: url('<?php echo get_the_post_thumbnail_url($postId) ?>')">
                                        <div class="relilab-termin-daytime">
                                            <?php echo date('H:i', strtotime(get_post_meta($postId, 'relilab_startdate', true))) . ' - ' . date('H:i', strtotime(get_post_meta($postId, 'relilab_enddate', true))) ?>
                                            <br>
                                            <h5>
                                                <a class="relilab-termin-title"
                                                   href="<?php echo get_post_permalink($postId) ?>">
                                                    <?php
                                                    echo $terminPost->post_title ?>
                                                </a>
                                            </h5>
                                        </div>

                                        <div class="wp-block-group relilab-meeting-button"
                                             onclick="location.href='<?php echo !empty(get_post_meta($postId, "relilab_custom_zoom_link", true)) ? get_post_meta($postId, "relilab_custom_zoom_link", true) : get_option('options_relilab_zoom_link') ?>'">
                                            👉 Zur Live Veranstaltung 👈
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
                        <div class="relilab-termin-box relilab-termin-empty relilab-termin-<?php echo $date->format('D'); ?>">
                            <div class="relilab-termin-date">
                                <div class="relilab-termin-day">
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
                                <div class="relilab-termin-spacer relilab-termin-<?php echo $whileDate; ?>"></div>  <?php
                                $whileDate = date('D', strtotime($whileDate . '+1 days'));
                            }

                            ?> </div> <?php
                            ?> </div> <?php
                        }
                        ?> </div> <?php
                        $newMonth = true;
                        $newWeek = true;
                    }

                }
                ?>
            </div>
            <?php

        } else {
            $currentMonth = '';
            $numberOfPosts = 0;
            foreach ($posts as $currentPost) {

                global $post;
                setup_postdata($currentPost);
                $post = $currentPost;
                if (($postRestriction == -1 || $numberOfPosts < $postRestriction) && $startDate <= date('Y-m-d', strtotime(get_post_meta(get_the_ID(), 'relilab_startdate', true)))) {
                    $numberOfPosts++;

                    if ($currentMonth != RelilabTermine::getMonat(get_post_meta(get_the_ID(), 'relilab_startdate', true))) {
                        $currentMonth = RelilabTermine::getMonat(get_post_meta(get_the_ID(), 'relilab_startdate', true));
                        ?>
                        <div class="relilab-list-month">
                            <h3> <?php echo $currentMonth . ' ' . date('Y', strtotime(get_post_meta(get_the_ID(), 'relilab_startdate', true))) ?> </h3>
                        </div>
                        <?php
                    }
                    if ($template = locate_template('relilab-Termine')) {
                        load_template($template);
                    } else {
                        load_template(dirname(__FILE__) . '/templates/single-termin-block.php', false);
                    }
                }
            }
            wp_reset_postdata();
        }

        return ob_get_clean();
    }

    function enqueue_scripts()
    {
        wp_enqueue_style('single-termin-block-style', plugin_dir_url(__FILE__) . 'css/style.css');
        wp_enqueue_script('termin-block-script', plugin_dir_url(__FILE__) . 'js/termin-script.js', array('jquery'), false, true);
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

}

new RelilabTermine();
