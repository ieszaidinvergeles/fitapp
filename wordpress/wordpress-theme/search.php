<?php
/**
 * search.php — Search Results Template
 * Displays search results
 */
voltgym_get_header();
?>
<main class="min-h-screen pt-10 pb-32 px-6 max-w-7xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-8">
        <section>
            <header class="mb-12">
                <h1 class="font-headline font-extrabold text-5xl md:text-7xl tracking-tighter mb-4 italic">
                    Search Results for: "<?php echo get_search_query(); ?>"
                </h1>
                <div class="h-[2px] w-12 bg-primary-container"></div>
            </header>

            <?php if (have_posts()) : ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php while (have_posts()) : the_post(); ?>
                        <article class="bg-surface-container rounded-xl p-6 border border-outline-variant/10">
                            <h2 class="font-headline font-bold text-xl mb-2">
                                <a href="<?php the_permalink(); ?>" class="hover:text-primary-container transition-colors">
                                    <?php the_title(); ?>
                                </a>
                            </h2>
                            <p class="text-on-surface-variant text-sm mb-4"><?php the_excerpt(); ?></p>
                            <time class="text-[10px] text-zinc-500 font-bold uppercase tracking-widest">
                                <?php the_date(); ?>
                            </time>
                        </article>
                    <?php endwhile; ?>
                </div>
                <div class="mt-8"><?php the_posts_pagination(); ?></div>
            <?php else : ?>
                <p class="text-on-surface-variant">No results found for "<?php echo get_search_query(); ?>".</p>
            <?php endif; ?>
        </section>
        <?php voltgym_get_sidebarbar(); ?>
    </div>
</main>
<?php voltgym_get_footer(); ?>