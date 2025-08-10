<?php get_header(); ?><main class="container" style="padding:2rem 0;">
<?php if(have_posts()): while(have_posts()): the_post(); ?><article <?php post_class(); ?>>
  <h1><?php the_title(); ?></h1><div><?php the_content(); ?></div>
</article><?php endwhile; endif; ?>
</main><?php get_footer(); ?>
