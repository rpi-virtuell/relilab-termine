<div class="relilab-list-termin-box">
    <div class="relilab-list-termin-header">
        <?php echo RelilabTermine::getWochentag(get_post_meta(get_the_ID(), 'relilab_startdate', true)) . ' ' .
                date('j', strtotime(get_post_meta(get_the_ID(), 'relilab_startdate', true))) . '. ' .
                RelilabTermine::getMonat(get_post_meta(get_the_ID(), 'relilab_startdate', true)); ?>
        <br>
        <?php echo date('H:i', strtotime(get_post_meta(get_the_ID(), 'relilab_startdate', true))) . ' - ' . date('H:i', strtotime(get_post_meta(get_the_ID(), 'relilab_enddate', true))) ?>
    </div>
    <div class="relilab-termin-content">
        <div class="relilab-termin-thumbnail"
             style="background-image: url('<?php echo get_the_post_thumbnail_url(get_the_ID()) ?>')">
            <div class="relilab-termin-post-details">
                <h4 class="relilab-termin-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h4>
                <p class="relilab-termin-excerpt"><?php echo get_the_excerpt(); ?></p>
            </div>
        </div>
        <div class="relilab-meeting-button"
             onclick="location.href='<?php echo !empty(get_post_meta(get_the_ID(), "relilab_custom_zoom_link", true)) ? get_post_meta(get_the_ID(), "relilab_custom_zoom_link", true) : get_option('options_relilab_zoom_link') ?>'">
            👉 Zur Live Veranstaltung 👈
        </div>
    </div>
</div>
