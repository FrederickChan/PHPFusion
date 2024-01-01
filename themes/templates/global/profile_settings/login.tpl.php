<?php

function login_up_settings( $info ) {

    opentable( 'Manage account login sessions' );
    ?>
    <table class="table table-responsive">
        <thead>
        <tr>
            <th style="width:33%;">Time</th>
            <th>Device</th>
            <th>IP</th>
            <th>Operation</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ( !empty( $info['user_logins'] ) ) :
            foreach ( $info['user_logins'] as $rows ):
                ?>
                <tr>
                    <td>
                        <span class="small text-muted"><?php echo showdate( 'l', $rows['user_logintime'] ) . '</span><br>' . showdate( 'Y-m-d H:i:s e', $rows['user_logintime'] ) ?>
                    </td>
                    <td>
                        <span class="small text-muted"><?php echo ucfirst( $rows['user_device'] ) . '</span><br><p class="small">' . $rows['user_os'] . ', ' . $rows['user_browser'] . '</p>' ?>
                    </td>
                    <td class="align-middle"><?php echo $rows['user_ip'] ?></td>
                    <td class="align-middle">
                        <a href="<?php echo $rows['remove'] ?>" class="btn btn-primary-soft">Deauthorise</a>
                    </td>
                </tr>
            <?php

            endforeach;
        endif;
        ?>
        </tbody>
    </table>

    <?php
    closetable();
}
