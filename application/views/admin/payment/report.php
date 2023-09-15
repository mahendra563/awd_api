<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$emp_id = \Yii::$app->request->get("emp_id");
$empModel = \app\models\Employee::findOne($emp_id);
$month = \app\components\Helpers::i()->formatDate(\Yii::$app->request->get("month")."-01", "M Y");

$status = "<strong class='text-danger'>Unpaid</strong>";
if($summery["balance"] == 0){
    $status = "<strong class='text-success'>Paid</strong>";
}

?>
<style>
    .table-sm{
        font-size:10px;
        padding:3px;
    }
    .table-sm td, .table-sm th{
        padding:5px !important;
    }
    h4{
        padding:0px;
        margin:0px;
        font-size:16px;
    }
    hr{
        margin-top:3px;
        margin-bottom:3px;
    }
    .card{
        border:1px solid silver;
        margin-bottom:10px;
    }
    .card-header{
        background-color:rgba(0,0,0,0.1);
        padding:10px;
        
        border-bottom:1px solid silver;
    }
    .card-body{
        padding:5px;
    }
</style>
<div class="container" style="width:90%">
<h3>Report for <?= $empModel->emp_fullname ?> - <?= $month ?></h3>
<hr />
<br />

<?php foreach(["Hard","Soft"] as $type2){ ?>

<div class="card">
    <div class="card-header">
        <h4><?= $type2 ?> - Work</h4>
    </div>
    <div class="card-body">
        <h5>Work Records</h5>
<hr />
<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>Project</th>
            <th>Task</th>
            <th>Rate</th>
            <th>Qty</th>
            <th>Amount</th>
            <th>Completed On</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
<?php foreach($month_records["projecttasks"][$type2] as $r) { ?>
        <tr>
            <td><?= $r["cost"]["project"]["proj_title"] ?></td>
            <td><?= $r["task"]["task_title"] ?></td>
            <td><?= $r["ts_rate"] ?></td>
            <td><?= $r["ts_qty"] ?></td>
            <td><?= $r["ts_amount"] ?></td>
            <td>
                <?php if($r["ts_status"] == "Completed"){ ?>
                    <?= \app\components\Helpers::i()->formatDate($r["ts_completion_date"],"d M Y") ?>
                <?php } else { ?>
                Pending
                <?php } ?>
            </td>
            <td>
                <?= $status ?>
            </td>
        </tr>
<?php } ?>
    </tbody>
</table>

<h5>Incentive Records</h5>
<hr />

<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>Project</th>
            <th>Print Order</th>
            <th>Print Order Description</th>
            <th>Task</th>
            <th>Rate</th>
            <th>Qty</th>
            <th>Amount</th>
            <th>Incentive Rate</th>
            <th>Incentive</th>
            <th>Incentive Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($month_records["incentive"][$type2] as $r) { ?>
        <tr>
            <td><?= $r["projectTask"]["cost"]["project"]["proj_title"] ?></td>
            <td><?= \app\components\Helpers::i()->formatDate($r["projectPrintOrder"]["pr_date"], "d M Y") ?></td>
            <td><?= $r["projectPrintOrder"]["pr_description"] ?></td>
            <td><?= $r["projectTask"]["task"]["task_title"] ?></td>
            <td><?= $r["projectTask"]["ts_rate"] ?></td>
            <td><?= $r["projectTask"]["ts_qty"] ?></td>
            <td><?= $r["projectTask"]["ts_amount"] ?></td>
            <td><?= $r["projectTask"]["cost"]["cost_incentive_rate"] ?>%</td>
            <td><?= $r["inc_amount"] ?></td>
            <td><?= \app\components\Helpers::i()->formatDate($r["inc_date"],"d M Y") ?></td>
            <td><?= $status ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
 </div>
 
</div>
<?php } ?>
   


<?php /*
<h4>Payment Records</h3>
<hr />

<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>For Month</th>
            <th>Payment Date</th>
            <th>Mode</th>
            <th>Description</th>
            <th>Amount</th>            
        </tr>
    </thead>
    <tbody>
        <?php foreach($month_records["payment"] as $r) { ?>
        <tr>
            <td><?= \app\components\Helpers::i()->formatDate($r["pmt_month"], "M Y") ?></td>
            <td><?= \app\components\Helpers::i()->formatDate($r["pmt_date"], "d M Y") ?></td>
            <td><?= $r["pmt_mode"] ?></td>
            <td><?= $r["pmt_description"] ?></td>
            <td><?= $r["pmt_amount"] ?></td>            
        </tr>
        <?php } ?>
    </tbody>
</table>
*/ ?>

<div class="card">
    <div class="card-header">
        <h4>Summery</h3>
    </div>
    <div class="card-body">



<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th style="width:15%">Old Work</th>
            <th style="width:15%">Old Incentive</th>
            <th style="width:15%">Total Amount</th>
            <th style="width:15%">Target</th>
            <th style="width:15%">Achievement</th>
            <th style="width:15%">Incentive Earned</th>
            <th style="width:10%">Payments</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?= $month_summery["oldwork"]["project_work"] ?>
            <td><?= $month_summery["oldwork"]["incentive"] ?>
            <td><?= $month_summery["total_amount"] /*+ $month_summery["oldwork"]["project_work"]*/ ?></td>
            <td><?= $month_summery["target"] ?></td>
            <td><?= $month_summery["workdone"] ?></td>
            <td><?= $month_summery["incentive"] /* + $month_summery["oldwork"]["incentive"]*/ ?></td>
            <td><?= $month_summery["payment"] ?></td>
        </tr>
    </tbody>
</table>

    </div>
</div>

<?php /*
<div class="text-center">
    <h4>Needed To Pay On <?= $month ?> : Rs <?= $summery["balance"] ?></h4>
</div>

<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th></th>
            <th>(On <?= $month ?>)</th>
            <th>(Till <?= $month ?>)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th>Old Project Work</th>
            <td><?= $month_summery["oldwork"]["project_work"] ?></td>
            <td><?= $summery["oldwork"]["project_work"] ?></td>
        </tr>
        <tr>
            <th>Old Incentive</th>
            <td><?= $month_summery["oldwork"]["incentive"] ?></td>
            <td><?= $summery["oldwork"]["incentive"] ?></td>
        </tr>
        <tr>
            <th>Target</th>
            <td><?= $month_summery["target"] ?></td>
            <td>-NA-</td>
        </tr>
        <tr>
            <th>Total Amount</th>
            <td><?= $month_summery["total_amount"] ?></td>
            <td>-NA-</td>
        </tr>
        <tr>
            <th>Achievement</th>
            <td><?= $month_summery["workdone"] ?></td>
            <td><?= $summery["workdone"] ?></td>
        </tr>
        <tr>
            <th>Incentive Earned</th>
            <td><?= $month_summery["incentive"] ?></td>
            <td><?= $summery["incentive"] ?></td>
        </tr>
        <tr>
            <th>Payments</th>
            <td><?= $month_summery["payment"] ?></td>
            <td><?= $summery["payment"] ?></td>
        </tr>  
    </tbody>
</table> */ ?>

  </div>


<script>
    window.print();
    </script>
    <div class="text-center hidden-print">
        <button onclick="window.print()" class="btn btn-primary">
            Print
        </button>
    </div>
