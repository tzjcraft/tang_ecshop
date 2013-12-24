<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/lib_transaction.php');

$id = $_GET['id'];
$province_list = get_regions(1, $id);

?>

<?php foreach ($province_list as $province): ?>
    <?php $city_list = get_regions(2, $province['region_id']); ?>

    <?php
    $citys = array();
    foreach ($city_list as $city)
    {
        $citys[] = $city['region_name'];
    }
    ?>
    <?php echo $province['region_name']; ?> ( <?php echo implode(', ', $citys); ?> )
<br />
    <br />
    <br />
<?php endforeach; ?>


