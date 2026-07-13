<?php declare(strict_types=1);
class DashboardController {
    public function stats(): array {
        $emp        = new Employee();
        $att        = new Attendance();
        $leave      = new LeaveRequest();
        $salary     = new Salary();
        $dept       = new Department();
        $notif      = new Notification();

        $statuses   = $emp->countByStatus();
        $activeCount = 0;
        foreach ($statuses as $s) {
            if ($s['status'] === 'active') $activeCount = (int) $s['total'];
        }

        return [
            'total_employees'   => $activeCount,
            'present_today'     => $att->presentToday(),
            'pending_leaves'    => $leave->pendingCount(),
            'pending_salary'    => $salary->pendingCount(),
            'total_paid'        => $salary->totalPaid(),
            'dept_stats'        => $dept->stats(),
            'attendance_trend'  => $att->weeklyTrend(),
            'recent_hires'      => $emp->recentHires(5),
            'today_summary'     => $att->todaySummary(),
            'payroll_trend'     => $salary->monthlyPayroll(6),
            'notifications'     => $notif->forUser((int) $_SESSION['user_id'], 5),
            'unread_notif'      => $notif->unreadCount((int) $_SESSION['user_id']),
        ];
    }
}
