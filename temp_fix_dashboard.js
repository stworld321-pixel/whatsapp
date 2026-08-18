const fs = require('fs');
const path = 'resources/js/Pages/client/Dashboard.jsx';
let s = fs.readFileSync(path, 'utf8');

s = s.replace(/function TrackerBar\(\{ label, current, limit, percent, icon: Icon, accent = 'bg-brand-500' \}\) \{[\s\S]*?\n\}\n\nfunction PlanTrackerDropdown/, `function TrackerBar({ label, current, limit, percent, icon: Icon, accent = 'bg-brand-500' }) {
    const isUnlimited = limit === null || limit === undefined;
    const displayCurrent = current ?? 0;

    return (
        <div className="space-y-2">
            <div className="flex items-center justify-between gap-3">
                <div className="flex items-center gap-2 min-w-0">
                    <span className="inline-flex h-7 w-7 items-center justify-center rounded-full bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300">
                        <Icon className="h-3.5 w-3.5" />
                    </span>
                    <span className="truncate text-sm font-medium text-neutral-700 dark:text-neutral-300">{label}</span>
                </div>
                <span className="text-sm font-semibold tabular-nums text-neutral-900 dark:text-white">
                    {isUnlimited ? displayCurrent : `${displayCurrent}/${limit}`}
                </span>
            </div>
            <div className="h-2 overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700">
                <div
                    className={`h-full rounded-full transition-all ${accent}`}
                    style={{ width: `${isUnlimited ? 100 : clampPercent(percent)}%` }}
                />
            </div>
        </div>
    );
}

function PlanTrackerDropdown`);

s = s.replace(/function PlanTrackerDropdown\(\{ currentPlan, renewsAt, managedByAdmin, trackerRow, t \}\) \{/, 'function PlanTrackerDropdown({ currentPlan, renewsAt, managedByAdmin, trackerRows, t }) {');

s = s.replace(/const hasTracker = trackerRow && \(trackerRow\.limit !== null \|\| trackerRow\.current > 0\);\n    const primary = hasTracker \? trackerRow : null;/, 'const hasTracker = Array.isArray(trackerRows) && trackerRows.length > 0;\n    const primary = hasTracker ? trackerRows[0] : null;');

s = s.replace(/\{primary\.current\}\/\{primary\.limit \?\? 'âˆž'\}/, '{trackerRows.length} trackers');

s = s.replace(/\{hasTracker \? \(\n\s*<div className="space-y-3">\n\s*<TrackerBar \{\.\.\.trackerRow\} \/>\n\s*<\/div>\n\s*\) : \(/, `{hasTracker ? (
                                <div className="space-y-3">
                                    {trackerRows.map((row) => {
                                        const Icon = row.icon ?? MessageSquare;
                                        return (
                                            <TrackerBar
                                                key={row.key}
                                                label={row.label}
                                                current={row.current}
                                                limit={row.limit}
                                                percent={row.percent}
                                                icon={Icon}
                                                accent={row.accent ?? 'bg-brand-500'}
                                            />
                                        );
                                    })}
                                </div>
                            ) : (`);

s = s.replace(/const currentUsage = usePage\(\)\.props\.current_workspace_usage \?\? \{\};\n    const hasTeamLimit = team_members_limit !== null && team_members_limit !== undefined && Number\(team_members_limit\) > 0;\n    const membersLabel = hasTeamLimit \? `\$\{team_members_count\}\/\$\{team_members_limit\}` : `\$\{team_members_count\}`;\n    const membersPct = hasTeamLimit \? clampPercent\(\(Number\(team_members_count\) \/ Number\(team_members_limit\)\) \* 100\) : null;\n    const trackerRow = \{[\s\S]*?\n    \};/, `const currentUsage = usePage().props.current_workspace_usage ?? {};
    const workspaceTracker = usePage().props.current_workspace_tracker ?? [];
    const hasTeamLimit = team_members_limit !== null && team_members_limit !== undefined && Number(team_members_limit) > 0;
    const membersLabel = hasTeamLimit ? \`${team_members_count}/${team_members_limit}\` : \`${team_members_count}\`;
    const membersPct = hasTeamLimit ? clampPercent((Number(team_members_count) / Number(team_members_limit)) * 100) : null;
    const trackerRows = workspaceTracker.length > 0 ? workspaceTracker : [
        {
            key: 'whatsapp_messages_per_month',
            label: t('client.whatsapp_messages') || 'WhatsApp messages',
            current: currentUsage.whatsapp_messages_per_month?.current ?? 0,
            limit: currentUsage.whatsapp_messages_per_month?.limit ?? planLimits.whatsapp_messages_per_month ?? null,
            percent: currentUsage.whatsapp_messages_per_month?.percent ?? 0,
            icon: MessageSquare,
            accent: 'bg-brand-500',
        },
    ];`);

s = s.replace(/trackerRow=\{trackerRow\}/, 'trackerRows={trackerRows}');

fs.writeFileSync(path, s, 'utf8');
