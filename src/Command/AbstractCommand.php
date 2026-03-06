<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Command;

use BusinessG\LaravelExcel\Progress\ProgressData;
use BusinessG\LaravelExcel\Progress\ProgressInterface;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

abstract class AbstractCommand extends Command
{
    /**
     * 显示进度
     *
     * @param $token
     * @return void
     */
    protected function showProgress($token)
    {
        $progress = app(ProgressInterface::class);
        $this->newLine();
        $progressRecode = $progress->getRecordByToken($token);
        if (!$progressRecode) {
            $this->error('未找到进度记录');
            return;
        }

        $bar = new ProgressBar($this->output, 0);
        $bar->setFormat("
<fg=magenta>🔄 任务进度监控</>
%stats%
%bar%
%message%
");

        $latestProgress = $progressRecode;

        $bar->setPlaceholderFormatter('stats', function () use (&$latestProgress) {
            $total = $latestProgress->progress->total ?? 0;
            $current = $latestProgress->progress->progress ?? 0;
            $success = $latestProgress->progress->success ?? 0;
            $fail = $latestProgress->progress->fail ?? 0;
            $remaining = max(0, $total - $current);

            $totalDisplay = $total > 0
                ? sprintf("总数: %d (进度: %d)", $total, $current)
                : sprintf("进度: %d", $current);

            return sprintf(
                "<fg=cyan>📊 %s</> | <fg=green>✅ 成功: %d</> | <fg=red>❌ 失败: %d</> | <fg=yellow>⏳ 剩余: %d</>",
                $totalDisplay,
                $success,
                $fail,
                $remaining
            );
        });

        $bar->setPlaceholderFormatter('bar', function () use ($bar, &$latestProgress) {
            $total = $latestProgress->progress->total ?? 1;
            $current = $latestProgress->progress->progress ?? 0;
            $percent = $total > 0 ? min(1, $current / $total) : 0;

            $barWidth = 30;
            $complete = (int)round($percent * $barWidth);
            $remaining = $barWidth - $complete;

            $color = match (true) {
                $percent >= 0.8 => 'green',
                $percent >= 0.5 => 'cyan',
                $percent >= 0.3 => 'yellow',
                default => 'red'
            };

            $percentDisplay = $total > 0 ? sprintf("%d%%", round($percent * 100)) : '';

            return sprintf(
                "<fg=%s>[%s%s]</> <fg=white>%s</>",
                $color,
                str_repeat('█', max(0, $complete)),
                str_repeat('░', max(0, $remaining)),
                $percentDisplay
            );
        });

        $bar->setPlaceholderFormatter('message', function () use (&$latestProgress) {
            $spinner = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];
            $spinnerChar = $spinner[time() % count($spinner)];

            $status = match ($latestProgress->progress->status) {
                ProgressData::PROGRESS_STATUS_COMPLETE => '<fg=green>✔ 处理完成</>',
                ProgressData::PROGRESS_STATUS_FAIL => '<fg=red>✖ 处理失败</>',
                ProgressData::PROGRESS_STATUS_OUTPUT => '<fg=green>✖ 上传中</>',
                default => sprintf('<fg=yellow>%s 处理中...</>', $spinnerChar)
            };

            return $status;
        });

        $bar->start();

        do {
            $latestProgress = $progress->getRecordByToken($token);
            if ($latestProgress) {
                if ($bar->getMaxSteps() != $latestProgress->progress->total) {
                    $bar->setMaxSteps($latestProgress->progress->total);
                }

                $bar->setProgress($latestProgress->progress->progress);

                $bar->display();

                usleep(100000);
            }
        } while (!in_array($latestProgress->progress->status, [
            ProgressData::PROGRESS_STATUS_COMPLETE,
            ProgressData::PROGRESS_STATUS_FAIL,
        ]));

        $bar->finish();
        $this->newLine();

        if ($latestProgress->progress->status === ProgressData::PROGRESS_STATUS_FAIL) {
            $this->error('处理失败: ' . ($latestProgress->progress->message ?? '未知原因'));
        } else {
            $this->table(['token', 'response'], [[$latestProgress->data->token ?? '', $latestProgress->data?->response ?? '']]);
        }
        $this->newLine();
    }
}
