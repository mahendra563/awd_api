<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<div class="container" style="width:95%">
<h3><?= $projModel->proj_title ?></h3>
<hr />
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Task ID</th>
            <th>Task Nature</th>
            <th>Qty</th>
            <th>Rate</th>
            <th>Amount</th>
            <th>Estimated Date</th>
            <th>Completion Date</th>
            <th>Status</th>
            <th>Alotted By</th>
            <th>Alotted To</th>
            <th>Operator</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $total = 0;
        foreach($taskModels as $m){ 
            $total += $m->ts_amount;
            ?>
        <tr>
            <td><?= $m->ts_id ?></td>
            <td><?= $m->task->task_title ?></td>
            <td><?= $m->ts_qty ?></td>
            <td><?= $m->ts_rate ?></td>
            <td><?= $m->ts_amount ?></td>
            <td><?= app\components\Helpers::i()->formatDate($m->ts_estimated_date,"d M Y") ?></td>
            <td>
                <?php if($m->ts_status == "Completed"){ ?>
                <?= app\components\Helpers::i()->formatDate($m->ts_completion_date,"d M Y") ?>
                <?php } ?>
            </td>
            <td>
                <?php if($m->ts_status != "Completed"){ ?>
                <strong><?= $m->ts_status ?></strong>
                <?php } else { ?>
                <?= $m->ts_status ?>
                <?php } ?>
            </td>
            <td><?= !is_null($m->alottedBy) ? $m->alottedBy->emp_fullname : "" ?></td>
            <td><?= !is_null($m->alottedTo) ? $m->alottedTo->emp_fullname : "" ?></td>
            <td><?= $m->user->user_name ?></td>
        </tr>
        <?php } ?>
        <tr>
            <td colspan="4"><strong>Total: </strong></td>
            <td><?= $total ?></td>
            <td colspan="5"></td>
        </tr>
    </tbody>
    
</table>

<?php if($by == "cost") { ?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Total Cost</th>
            <th>Used</th>
            <th>Remaining</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?= $costModel->cost_amount ?></td>
            <td><?= $total ?></td>
            <td><?= $costModel->cost_amount - $total ?></td>
        </tr>
    </tbody>
</table>
<?php } ?>

</div>

<script>
    window.print();
    </script>
    <div class="text-center hidden-print">
        <button onclick="window.print()" class="btn btn-primary">
            Print
        </button>
    </div>