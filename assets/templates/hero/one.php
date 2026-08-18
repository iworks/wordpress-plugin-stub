<?php
printf( '<li class="%s">', implode( ' ', get_post_class() ) );
printf( '<h3>%s</h3>', get_the_title() );
echo '<div class="post-inner">';
echo '<blockquote class="post-content">';
echo get_the_content();
echo '</blockquote>';
echo '</div>';
echo get_the_post_thumbnail( get_the_ID(), 'full' );
echo '<div class="post-excerpt">';
echo get_the_excerpt();
echo '</div>';
echo '</li>';