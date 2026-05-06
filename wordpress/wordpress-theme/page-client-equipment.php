<?php
/**
 * Template Name: Client Equipment
 */

require_login();
$GLOBALS['active'] = 'equipment';

$page = max(1, (int)($_GET['paged_equipment'] ?? 1));
$response = api_get('/equipment?page=' . $page, auth: true);
$resData = $response['result'] ?? [];



// Handle Laravel's paginated response structure
if (isset($resData['data'])) {
    $equipment = $resData['data'];
    $meta = $resData['meta'] ?? null;
} else {
    $equipment = $resData;
    $meta = null;
}

// Helper for images from the design
function get_equipment_placeholder($name) {
    $placeholders = [
        'Barbell'            => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBpm8eAXmvSPJFa92EkoZkaHxCsAQaFnYqtFpGnJwc_oKxBkWNX-ygbfLPjc3GYr_VZgqwKoJvY1czCe1shqC2GBoXYiF-RRPWKyeeFT9SXKyCuoM52-BO95qo-BV4FfNUWJIqpjGpG5URGHnfio-ggNRW-w_7N1Aj6Qgply5qU4yVRVwft5iKoDXJF3vSBf6pbnqlvbb1DLyv0nsAxhKblwcJtS6E-2uQ5NLpPumMqmEuVuR89H1hzz_xfpVLoX7SWbngJQkfQRO0a',
        'Bench'              => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAqdlOhVo4RoTLRxl0GtY2Fty2xu_2aWIQPehYqmelGlNk7dApVu-nbCEIPjFv0SferLNFD4Q3_MF92ZAgFw0KjIWWkDJh6Fbt7Bp_WMVFU5P14TBcNFaarEGmJZK1HN5Aep0pDaYFVxe5UVBJsNjWzdZLEhfRmRN4OrjVebXzga2O_cjDeOqjZJ0XTMs-OtATfoynD5wkxcOojSSGQAxk101nXYMVevaJpbhwPV3Rzwv7DCFdOxWZ92_sQuJTyPspb-H3e-lYL_ItD',
        'Kettlebell'         => 'https://lh3.googleusercontent.com/aida-public/AB6AXuA-9f_KKpT1jDKcGFIAxiq47WpK1Gy7V61V9KsKAJgdGQUqw5vvp7vCgswzCj-ZD-WN8zK2-8WfNdu6EJd6x2k2GizNeVjzWZ_eMSqzFfPZuDcfrAu1kUHOprEbsSVPuqHHPNg2KS6-OALfFYq9VHn-tNiEd38xPBWrdYft5xT3v7I4Kf1_PR2Ey4GxBDz7DYvYzK5CzRD2wHP59OBuRoYP5xR2w5YHAFKmeYg08ERwN-2NpW77bNoNXFRXKwaEYcv1Aa0YmGGApL0Y',
        'Squat Rack'         => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAl3bsm3R6ub2UsEdeGqNy4SZmFLJBKPkly2w-1fQ2ROqbUKYzpw9Gpb428w8BAWrS2bQhwuq1V28jPK1_kAHH3n_YrSP5vnsPIUkybPlauycDgqeml8zlaOT0IovPOZod73VjtE9ydk_wxx5e8yMlLkv081qPi4UhOMFTeDHfAWn4Yd9cqyB_K1fbMNKNluh7X7xyEoScrmmN1PThSJUdyizneUP_79ASuCxwjepiM8_LxIWDkZtCLJDUR6UisvYNg0vOUzyUeQ6po',
        'Pec Deck'           => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBEfebUuF8B2l2X-LWK2CZO0u7NqkNl1Hi6U_SqGwVWlLng6LVWFTREL5NW-ZY4ViMkTjK_oMi9oymE62IdCqVc6MKZwUWAW_d-hd9f1LilpTQI9xqMdjO4rODVdWAToeXSgT8aZFnEoyapgyvnTquZVwiHXHH08lhBTlVlRHHgTJIv13M0pGJuzp67DFkXptk1zoAlZxluuc9gl2HndM0rhTzYou3QXZA5IZ2hAq-zs8jB0n_hv_ZPj4LgN5CLcWY3yC_RxInyjEl8',
        'Lat Pulldown'       => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCLc3_8incLY0kegFHYr5wHA0P-hRLxhfnC3AWL0nHG3oEdySwkoNF-YijJPhbSEtI2Pw4E9-Ui1qnRjlLJh7QWbO00fWq_WppG5AxgMF8zYMbrlRKcM1NgGSDwPkXJuAHgvG1yvd0ZIxKCzpal_5VEa5hXlDnEYvydxRtunhLzvbiiYZp-3wkHHWSM0J1QMkapnT-74jBsH5BGYTh4mS6b5UHlOy4h4O967U2AGmo_fdlUWee4cDHBMnZA2TRb9wJ_EqBYT7hgLP0E',
        'Battle Ropes'       => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCXtDLBOzSX352cm2sh-Fbx9HCF17tfb3JsXbFDDf-gUerZaVj2OZyyOiEJqgomxKPbBD_npa5Kn43GrXLLXiUxBWPtzMIGR5TuaVYJR0qt4ndZ7DK8Iihj1y6zv3sxPbpdoDL15icoqg-cr1eX8oezbRu-pwvrVIb89jMuYCBFNw_FfeXQ3LzTR067FGKqCOZnmPlW6371C61yMmTsFO6zVbszqS9zkIyRagqa9lmWUwOMvFnDc25Up_N53VXIGpu1vV3Ro5GbxsYk',
        'TRX'                => 'https://lh3.googleusercontent.com/aida-public/AB6AXuD_6R09DUbazNHJlEKjDCUZn5-r39eKVn7TwGTywMTqbkM5i1T19tWrpiJPTLy3kfTt-LsMDmHkYh-ACwjM9V4cyNb1vZm-o-Pn4ElssjOY6TRRG17qWMujQcVQlVzCc7nligMV_Pg-ZLWTmlXP046DF3Ap71Dite7OJlkjfcRLbTRhCeAufuDdIhnofh3F2bzeC64t5elxfdG0FZ_HfqThBqg-vtpV4hH8DrbZne0_0ugQnR6a4Zj78gPYKcdaEu09biB4Abw_vMBB',
        'Dumbbell'           => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAMDWUx2H40JUIq9z0JRFQqfC4HIVxq1GW921TKI4mhaasm15JfI7ko7ikd-d6DaK2fnyDuQb1g5waDG8fb0Mu4IivcdlqlHF3HPR4rXiC-EVuNhEe3NsA6EAXBo9cCfDYg90KZ7PPQplWwfJ68WlTdGROLcD-yJdUfxXSZEmXV4h0Dmc06QI-eAmTyzvlufVDXSP_LTs7W25KXhphEfBDxB5ciPw_dZqxigUgt7vGSO6zpvEGu5_1P7YcxQ5S91FNqZX303g5oKA3p',
        'Leg Press'          => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDITtrtzKeehtEZu0c6OkIiWC0GfERzTPTLFwCcnbRJD56hZcdrzBrv1Ff6D05KYkeb5pn8Wb6LrCTBX7zwU3tEroF7W7Fgzg_likraHkH9_kSrBuFaFxC-WmbYTpWt6hQzUkO_1UhPdxBxJUo3vC_33q5iiuhF3-1J_hclbbtfUSEBs19y9yszfzj-RTbd4lXBu1PFl03k2ppo3aKRIjRjYxywSSg85NV__Q2HuURCh6fLYLsRn95G885W1ft-NflcXPUio8Ckldih',
        'Smith Machine'      => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAzWgfbSvExHTsP1Y2ctbDRzUgDyfIALk3pIjw3r-SBVmXL4SR0Uo8sKZRFqTw01vrBkt4daqXZgYVL3vR08afEJvF3N_QOcgA9KxiM97DcSk0rbPxnbKyMsVVCF7CkvcaP8Z1UIEaQ_5192pAvSUVYFVyMvFJW_A6azIJdKOVGdcvTiW18ub-lT1YUveYmx6nizVkV0joeJEtJ-EqZ8fwdJiu9zT5PSYNZ8qA70dpf_megpL0fennrD82m41wZkS4zHXYeG_jyd3e9',
        'Bike'               => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBtUiSRJMguJekpJ1GAgMnx8U0MQBMuT3_LJQTN9LWf70CCjhS2_7kecKW6oszvLani2be7GwXQr8yXoDC1PlIC8K2RpP1UeTWe_ZQq_6el1KYwh6KPAy8gJ8psvaSSc91EeBcgvCHVQ5VJGy-xkNTyHLg-WMTwf0WnaaYhQ5mHiI2V8S2p7lJDLAdCTlJ3LqMvfXRLmi3Cy7PsOnpscMMsaDaQeH6GjADHZPGdoQinFYuhNqkJofx4PMCnB0lNVlMx9l0c4J5ylIUq',
        'Rower'              => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAriyvD0DrDOKkCgCcahpLyrEtAl8nAupgKsBXQj3jvu-MgfLozlHm2o8sN0MbOg9Lu863dHod9h_6HreYVTPZasmSGltQpCxjhB6LkG4FEJIQ8ZCux6dxEz6x_j5x57cHw4Asdww9Jcv4kMj28PA_BXjGvSaZbYmpHUUz_ElEc5OWQCh6thupCTXMpzA_SkJ8iYUn7FO-NitejNljel6km2eKWhA8o9OT-0ZeZA7i9-HInVTeuWQn-dxJzs9hu8qM6acILn984wtbO',
        'Medicine Ball'      => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDexUMMEBSjTSJRiiFeIFLw91U6dCBU3S0Z8ozBSsOgCULT0p0D6aPfLOGcw5upkn4PSL0WO6ultOEh0Q2kVVKT4bQ8O8ydqTnvxRgkyjiTHw1ou3dkqlGtLYCFCXcsKWn6SB0UFhQAy5Jv8mNyivvZujZMdQaiIUccmNb1HBnJ1Z3HS330tYaY943fwX36ACWKRMIoWd2ara0dr1wn9RWJXynbicVuZViMcXdTk5PyOjTwLID5iAAsFOV0bOxiB4rOeDgBSTAnaoxJ',
        'Roman Chair'        => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBwCgOOusQNUOHG0ZBie3cTkW-8sBuYWiy8CjdTTWLMfIsax3juQmqsr6qwZpXG7Lo9LBXzgOOFgZUTHH3-i51FMP74e7r-fzegLWGiPpsj6v_5bemONXID6zCmdDadPEVa7-GZRqr18icFWoevNaOrNFYmFYVHcTuIlZzObxUtMfUhjS8hT4nvUTCpTXTq2G-NyruIRl1-B5U08LaJ2mwE9PZnh3tDSSRFvHIdRcWBwRGotbd-pWWxBXF7XiH_ywOwqPJSxzdx2Qr-',
        'Plyo Box'           => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDwJdGTO-29RigNxXWgpUzORjeg5p2o4GV6ckf96e5_ggqzBwUOCwFZJr20ay1_97eDfxf-3Wij-oaG_y7R0WEHIfaz-NnZFssR5vnD2JbL0KnoygFL1dADJFujRm50dVWpbnzK0Qzwk5_9t4fDEvr96Xw0xSMe1kKOwjszIYNih6p8QL9o0aIH7qHTIcGrEulYdRh-0KifnZ8maENVGoslyVVXTU4zhS1qSEVvasxPhWksUpTKZLz9obNF1y8ptSzqcU6HNZMKHZnT',
        'Cable'              => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCS0Xtsubd0VTBy2BEwWIGMTjdUrsq_a9N1xyy-YrrxXnJDSd9z6aNp4KBrcMJbiRcmA11IieLE8ifNid4xdXlP6dDOx0tVWgyVt-iCTFyQ5kGmLREochpr-ZklZQ4OoBOHS2zhWOAu3JwMgkFWDyeRb7FIe8JI4FgjU7WmykQsXeybyYoWwSwU3c7z4_VTmZ0DsJhIjTww7gnjeUMIzBg-CXuD9k6M2GjF37geGViBYooGfeML2Kfej0hV_Wq0OrfLOzJX8ecn0roT',
        'Hack Squat'         => 'https://lh3.googleusercontent.com/aida-public/AB6AXuB4WQwP-xjoPdv8KQEIMx_RItOOUHg3bXkEGzyN8JafkWmhcIqUDsXrQGpAB1YIFD-JrUQ2rBMVkeBRrw0NVoxj7Ifee6eHFoZWvrdeHNPGxrxdilUnD_Q9UEGQGGi8UnV1vK-mgtgfKAUjpZB6iYCWilMyWCcNiFOa8a4YTmIBOFIHpImArCkUKOU7xVKkSKGofBgwMbfbQZoZCJI0zOJtyyMFt9zRuJrTQ_ldTMwEEJNK6YVDJ1xJk2OxFDZK5ykokTh_6wD2CRnU',
        'Resistance Band'    => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDKZXctZLt_C9bJ84ISkGV8JNWHfkcD67MQxHim-_gg5jlY1tFLYUGnf_CilpFOvQLAEvp0ArFEg-H-pq4kKcopGOLzKkset8eo1lSleS3GsjDXKtNUDntCrj11JTJcMztbEPabmft9BSsOVCPDY8rZcm84PHM7mWIhIxXDuXGSrgzYEBqoJ7GDhhjyOwDBR16PhfI4mQoK8sEhGd5UUIF2LQFhOx_Kc5QhZyuvTW9Wyw0en0NACrOLa_EDVFLW6OwetrSuz1mqh4BJ',
        'Treadmill'          => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDRx0KVqeRCWy7IAx1oX0iS5LAZvUld_QKR10qlpQGzVmia9JRZxK6JkLjCi0EN2Gt0VOi7BNP5e-AcgQ5lcYNyCs-rmileatpB6GU3aEPpHfytPd6SeT8zXHLKQOkkaWJ5y8Knjl0KIRGoGTg0CFUCDLIj8m6xWfK_zc4JUCy4HxR6zdOYPDE49ki-NmYgxEI2eAmqEhPdCUFFZo58uSep6cmZcmGpEY-sz2p-X-MYx6GjbW4XGPahky4NKok99L2AG8G4E91YNQNt',
    ];

    foreach ($placeholders as $key => $url) {
        if (stripos($name, $key) !== false) {
            return $url;
        }
    }

    return 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1470&auto=format&fit=crop';
}

wp_app_page_start('Equipment Vault');
?>

<div class="mb-12 relative overflow-visible">
    <h1 class="font-display font-black text-6xl md:text-8xl tracking-tight text-on-surface uppercase mb-2 leading-none" style="letter-spacing: -0.04em;">
        EQUIPMENT
        <br/>
        <span class="text-primary-container">VAULT</span>
    </h1>
    <p class="font-body text-on-surface-variant text-lg md:text-xl font-medium tracking-wide border-primary-container ml-1">Core equipment for athletic evolution.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($equipment as $item): 
        $img = $item['image_url'] ?? get_equipment_placeholder($item['name']);
        $tag = $item['is_home_accessible'] ? 'HOME ACCESSIBLE' : 'PRO FACILITY ONLY';
        $itemNum = str_pad($item['id'], 3, '0', STR_PAD_LEFT);
    ?>
        <article class="relative h-[480px] rounded-xl overflow-hidden group">
            <img class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" 
                 src="<?= esc_url($img) ?>" 
                 alt="<?= esc_attr($item['name']) ?>">
            
            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent"></div>
            
            <div class="absolute inset-0 p-0 flex flex-col justify-between">
                <div class="flex justify-end">
                    <div class="bg-primary-container text-on-primary-container text-[10px] font-label font-black px-4 py-1.5 rounded-full uppercase tracking-widest shadow-xl mt-4 mr-4">
                        <?= h($tag) ?>
                    </div>
                </div>
                
                <div class="p-8 space-y-4">
                    <div class="flex justify-between items-end">
                        <div>
                            <span class="text-primary-container text-xs font-black tracking-widest uppercase mb-1 block">#<?= $itemNum ?></span>
                            <h2 class="font-headline font-black text-3xl text-white leading-tight uppercase">
                                <?php 
                                    $parts = explode(' ', $item['name'], 2);
                                    echo h($parts[0]);
                                    if (isset($parts[1])) echo '<br/>' . h($parts[1]);
                                ?>
                            </h2>
                        </div>
                    </div>
                    <p class="font-body text-white/80 text-sm max-w-[85%] leading-relaxed">
                        <?= h($item['description']) ?>
                    </p>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<?php if (!$equipment): ?>
    <div class="text-center py-20 bg-surface-container/30 rounded-3xl border border-dashed border-outline-variant/30 max-w-2xl mx-auto">
        <span class="material-symbols-outlined text-6xl text-zinc-800 mb-4">fitness_center</span>
        <p class="text-zinc-500 font-medium">No equipment found.</p>
    </div>
<?php endif; ?>

<!-- Pagination -->
<?php
if ($meta && ($meta['last_page'] ?? 1) > 1):
    $currentPage = $meta['current_page'];
    $lastPage = $meta['last_page'];
    $baseQuery = '?pagename=client-equipment';
?>
<div class="mt-12 flex items-center justify-center gap-4">
    <?php if ($currentPage > 1): ?>
        <a href="<?= $baseQuery ?>&paged_equipment=<?= $currentPage - 1 ?>" class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-zinc-400 hover:text-primary transition-colors border border-outline-variant/20">
            <span class="material-symbols-outlined">chevron_left</span>
        </a>
    <?php endif; ?>
    
    <div class="px-6 py-3 rounded-2xl bg-surface-container border border-outline-variant/20 text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500">
        Page <?= $currentPage ?> / <?= $lastPage ?>
    </div>
    
    <?php if ($currentPage < $lastPage): ?>
        <a href="<?= $baseQuery ?>&paged_equipment=<?= $currentPage + 1 ?>" class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-zinc-400 hover:text-primary transition-colors border border-outline-variant/20">
            <span class="material-symbols-outlined">chevron_right</span>
        </a>
    <?php endif; ?>
</div>
<?php endif; ?>

<div aria-hidden="true" class="h-[90px] w-full transparent"></div>

<?php wp_app_page_end(); ?>
