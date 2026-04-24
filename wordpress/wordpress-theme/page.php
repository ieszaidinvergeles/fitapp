<?php
/**
 * page.php — Default Page Template
 * Displays standard pages
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
                    </header>

                    <div class="prose prose-lg max-w-none text-on-surface">
                        <?php the_content(); ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </section>
        <?php voltgym_get_sidebarbar(); ?>
    </div>
</main>
<?php voltgym_get_footer(); ?>