<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "leave_account".
 *
 * @property int $txn_id
 * @property string $txn_date
 * @property string $txn_type
 * @property string $txn_leave_type
 * @property int $txn_amount
 * @property string $txn_comment
 * @property int $emp_id
 *
 * @property Employees $emp
 */
class LeaveAccount extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'leave_account';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['txn_date', 'txn_type', 'txn_leave_type', 'txn_amount', 'emp_id'], 'required'],
            [['txn_date'], 'safe'],
            [['txn_type'], 'string'],
            [['txn_amount', 'emp_id'], 'integer'],
            [['txn_amount'], 'validateTxn'],
            [['txn_leave_type'], 'string', 'max' => 45],
            [['txn_comment'], 'string', 'max' => 255],
            [['txn_comment'], 'default', 'value'=>''],
            [['emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['emp_id' => 'emp_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'txn_id' => 'Txn ID',
            'txn_date' => 'Date',
            'txn_type' => 'Type',
            'txn_leave_type' => 'Leave Type',
            'txn_amount' => 'Number of Days',
            'txn_comment' => 'Comment',
            'emp_id' => 'Employee',
        ];
    }
    
    public function validateTxn(){
        if($this->isNewRecord){
            if($this->txn_type == "Debit"){
                $balance = self::getAccountBalance($this->emp_id, $this->txn_leave_type);
                if(($balance - $this->txn_amount) < 0){
                    $this->addError("txn_amount","Not Enough Balance in ".$this->txn_leave_type." Leave");
                }
            }
        } 
    }

    /**
     * Gets query for [[Emp]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(Employee::className(), ['emp_id' => 'emp_id']);
    }
    
    
    
    public static function getAccountBalance($emp_id,$type=""){
        if($type == ""){
            $credit = self::find()
                    ->where(["emp_id"=>$emp_id,"txn_type"=>"Credit"])->sum("txn_amount");
            $debit = self::find()
                    ->where(["emp_id"=>$emp_id,"txn_type"=>"Debit"])->sum("txn_amount");
            return $credit-$debit;
        }
        
        $credit = self::find()
                ->where(["emp_id"=>$emp_id,"txn_type"=>"Credit","txn_leave_type"=>$type])->sum("txn_amount");
        $debit = self::find()
                ->where(["emp_id"=>$emp_id,"txn_type"=>"Debit","txn_leave_type"=>$type])->sum("txn_amount");
        return $credit-$debit;
    }
    
    public static function assignLeaves($emp_id, $dates){
        $balance = self::getAccountBalance($emp_id);
        $requested_days = count($dates);
        if($requested_days > $balance){
            return false;            
        } 
        
        $doTxn = function($emp_id, $type, $reduce, $comment){
            $model = new self();        
            $model->txn_date = date("Y-m-d H:i:s");
            $model->txn_type = "Debit";
            $model->emp_id = $emp_id;
            $model->txn_leave_type = $type;
            $model->txn_comment = $comment;            
            $model->txn_amount = $reduce;        
            if($model->validate()){
                $model->save();
            }
        };
   
        $paid = self::getAccountBalance($emp_id, "Paid"); 
        $reduce_paid = $requested_days <= $paid ? $requested_days : $paid;           
        $dts = array_slice($dates, 0, ($reduce_paid));
        $doTxn($emp_id, "Paid", $reduce_paid, implode(", ", $dts));
        $requested_days = $requested_days - $reduce_paid;
        
        
        if($requested_days == 0){
            return true;
        } else {
            $casual = self::getAccountBalance($emp_id, "Casual"); 
            $reduce_casual = $requested_days <= $casual ? $requested_days : $casual;      
            $dts = array_slice($dates, ($reduce_paid), $reduce_casual);
            $doTxn($emp_id, "Casual", $reduce_casual, implode(", ", $dts));            
            $requested_days = $requested_days - $reduce_casual; 
        }
        
        if($requested_days == 0){
            return true;
        } else {
            $special = self::getAccountBalance($emp_id, "Special"); 
            $reduce_special = $requested_days <= $special ? $requested_days : $special; 
            $dts = array_slice($dates, ($reduce_paid+$reduce_casual), $special);
            $doTxn($emp_id, "Special", $reduce_special, implode(", ", $dts));            
            $requested_days = $requested_days - $reduce_special;             
        }
        
        if($requested_days == 0){
            return true;
        }
        
        return true; //DEFAULT
    }
    
    
}
