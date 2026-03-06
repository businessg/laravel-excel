<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('excel_log', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->default('')->comment('唯一标识');
            $table->enum('type', ['export', 'import'])->default('export')->comment('类型:export导出import导入');
            $table->string('config_class', 250)->default('')->comment('配置类');
            $table->json('config')->nullable()->comment('config信息');
            $table->string('service_name', 20)->default('')->comment('服务名');
            $table->json('sheet_progress')->nullable()->comment('页码进度');
            $table->json('progress')->nullable()->comment('总进度信息');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态:1.待处理2.正在处理3.处理完成4.处理失败5.输出中6.完成');
            $table->json('data')->nullable()->comment('数据信息');
            $table->string('remark', 500)->default('')->comment('备注');
            $table->string('url', 300)->default('')->comment('url地址');
            $table->timestamps();

            $table->unique('token', 'uniq_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excel_log');
    }
};
