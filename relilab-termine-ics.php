<?php
function ical() {

    if(isset($_GET['relilab-termine-format']) && $_GET['relilab-termine-format'] == 'ics'){
        require_once __DIR__ . '/calendar/autoload.php';

        $posts = get_posts(array(
            'post_type'			=> 'post',
            'posts_per_page'	=> -1,
            'category'          => 'termine',
            'meta_key'			=> 'relilab_startdate',
            'orderby'			=> 'meta_value',
            'order'				=> 'ASC'
        ));

        date_default_timezone_set('Europe/Berlin');

        $events =[];

        foreach ($posts as $post){
            $startdate = get_post_meta($post->ID, 'relilab_startdate',true);
            $enddate = get_post_meta($post->ID, 'relilab_enddate',true);


            $event = (new Eluceo\iCal\Domain\Entity\Event())
                ->setSummary($post->post_title)
                ->setDescription(wp_trim_words(strip_tags($post->post_content),100,'...')."\n\n Zum Live-Event online: ".get_option('options_relilab_zoom_link'))
                ->setOccurrence(new Eluceo\iCal\Domain\ValueObject\TimeSpan(
                        new Eluceo\iCal\Domain\ValueObject\DateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i:s',fdate($startdate)),true),
                        new Eluceo\iCal\Domain\ValueObject\DateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i:s',fdate($enddate)),true),
                    )
                )
                ->setLocation(new Eluceo\iCal\Domain\ValueObject\Location('relilab-cafe','Relilab Cafe'))
                ->addAlarm(
                    new Eluceo\iCal\Domain\ValueObject\Alarm(
                        new Eluceo\iCal\Domain\ValueObject\Alarm\DisplayAction('Erinnerung: '.$post->post_title.' in 15 Minuten!'),
                        (new Eluceo\iCal\Domain\ValueObject\Alarm\RelativeTrigger(DateInterval::createFromDateString('-15 minutes')))->withRelationToEnd()
                    )
                );

            $events[] = $event;
        }
        $calendar = new \Eluceo\iCal\Domain\Entity\Calendar($events);
        $iCalendarComponent = (new \Eluceo\iCal\Presentation\Factory\CalendarFactory())->createCalendar($calendar);
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="relilab_cal.ics"');

        echo $iCalendarComponent;

        die();
    }
}

function fdate(string $date, $format = 'Y-m-d H:i:s'){
    return date($format,strtotime($date));
}