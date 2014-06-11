<?php
if ( isset( $_GET['product_id'] ) && $_GET['product_id'] != '-1' ) {
    $product_id = (int) $_GET['product_id'];
    $users = $this->get_users( $product_id );
    $product = get_post( $product_id );
} else {
    $product_id = '-1';
}
?>

<form action="<?php echo admin_url( 'admin.php' ) ?>" method="GET" style="margin: 20px 0;">

    <input type="hidden" name="page" value="wc-reports">
    <input type="hidden" name="tab" value="customers">
    <input type="hidden" name="report" value="customer-list-product">

    <select name="product_id" id="product_id">
        <?php foreach ($products as $key => $value) { ?>
            <option value="<?php echo $key; ?>"<?php selected( $product_id, $key ); ?>><?php echo esc_attr( $value ); ?></option>
        <?php } ?>
    </select>

    <?php submit_button( __( 'View Users', 'wc-customer-by-order' ), 'primary', 'submit', false ); ?>
</form>

<?php
if ( isset( $_GET['product_id'] ) && $_GET['product_id'] != '-1' ) {

    $emails = '';

    if ( $users ) {
        ?>

        <h2><?php printf( __( 'Showing users for: %s', 'wc-customer-by-order' ), $product->post_title ) ?></h2>

        <p>
            <?php printf( '%d result(s) found!', count( $users ) ); ?>
        </p>

        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e( 'Order ID', 'wc-customer-by-order' ); ?></th>
                    <th><?php _e( 'User ID', 'wc-customer-by-order' ); ?></th>
                    <th><?php _e( 'Email', 'wc-customer-by-order' ); ?></th>
                    <th><?php _e( 'Order Status', 'wc-customer-by-order' ); ?></th>
                    <th><?php _e( 'Date', 'wc-customer-by-order' ); ?></th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($users as $user) {

                    $emails .= $user->user_email . ', ';
                    ?>

                    <tr>
                        <td><a href="<?php echo admin_url( 'post.php?post=' . $user->order_id . '&action=edit' ); ?>"><?php echo $user->order_id ?></a></td>
                        <td><a href="<?php echo admin_url( 'user-edit.php?user_id=' . $user->user_id ); ?>"><?php echo $user->user_id ?></a></td>
                        <td><?php echo $user->user_email ?></td>
                        <td><?php echo $user->order_status ?></td>
                        <td><?php echo date_i18n( 'j F, Y', strtotime( $user->post_date ) ); ?></td>
                    </tr>

                <?php } ?>
            </tbody>
        </table>

        <h3><?php _e( 'Select Emails', 'wc-customer-by-order' ); ?></h3>
        <textarea rows="3" cols="60"><?php echo $emails; ?></textarea>

        <?php
    } else {
        echo '<div class="updated error"><p>' . __( 'No users found!', 'wc-customer-by-order' ) . '</p></div>';
    }
}