<?php

function blacklist_up_settings( $info ) {

    opentable( 'Manage account blacklists' );
    ?>
    <table class="table table-responsive" id="<?php echo fusion_table( 'uid_blacklist' ) ?>">
        <thead>
        <tr>
            <th>User</th>
            <th>Date</th>
            <th>Operations</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td colspan="3">Roadmap item development in progress</td>
        </tr>
        </tbody>
    </table>
    <?php
    closetable();

}
