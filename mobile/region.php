<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/lib_transaction.php');
$country_list = get_regions();
?>
<ul>
<?php foreach ($country_list as $country): ?>
            <li><a href="region2.php?id=<?php echo $country['region_id']; ?>"><?php echo $country['region_name']; ?></a></li>
        <br/><br/>
<?php endforeach; ?>
</ul>

