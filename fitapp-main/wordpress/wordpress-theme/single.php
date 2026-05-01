<?php
/**
 * single.php — Single Post Template
 * Displays individual posts
 */
voltgym_get_header();
?>
<main class="min-h-screen pt-10 pb-32 px-6 max-w-7xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-8">
        <section>
            <?php while (have_posts()) : the_post(); ?>
                <article class="bg-surface-container rounded-3xl p-8 md:p-12 border border-outline-variant/10">
                    <header class="mb-8">
                        <h1 class="font-headline font-extrabold text-4xl md:text-6xl tracking-tighter mb-4 italic">
                            <?php the_title(); ?>
                        </h1>
                        <div class="flex items-center gap-4 text-sm text-on-surface-variant">
                            <time class="font-bold uppercase tracking-widest">
                                <?php the_date(); ?>
                            </time>
                            <span class="text-primary-container">•</span>
                            <span>By <?php the_author(); ?></span>
                        </div>
                    </header>

                    <div class="prose prose-lg max-w-none text-on-surface">
                        <?php the_content(); ?>
                    </div>

                    <footer class="mt-12 pt-8 border-t border-outline-variant/20">
                        <div class="flex flex-wrap gap-2">
                            <?php the_tags('<span class="text-xs font-bold uppercase tracking-widest text-primary-container">Tags:</span> ', ', ', ''); ?>
                        </div>
                    </footer>
                </article>
            <?php endwhile; ?>
        </section>
        <?php get_sidebar(); ?>
    </div>
</main>
<?php voltgym_get_footer(); ?>