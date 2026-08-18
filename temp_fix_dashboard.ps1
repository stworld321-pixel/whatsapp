$path = 'resources/js/Pages/client/Dashboard.jsx'
$content = Get-Content $path -Raw

$content = $content -replace '(?s)function TrackerBar\(\{ label, current, limit, percent, icon: Icon, accent = ''bg-brand-500'' \}\) \{.*?\n\}\n\nfunction PlanTrackerDropdown', @'
function TrackerBar({ label, current, limit, percent, icon: Icon, accent = 'bg-brand-500' }) {
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

function PlanTrackerDropdown'@

$content = $content -replace 'function PlanTrackerDropdown\(\{ currentPlan, renewsAt, managedByAdmin, trackerRow, t \}\) \{', 'function PlanTrackerDropdown({ currentPlan, renewsAt, managedByAdmin, trackerRows, t }) {'
$content = $content -replace 'const hasTracker = trackerRow && \(trackerRow.limit !== null \|\| trackerRow.current > 0\);\n    const primary = hasTracker \? trackerRow : null;', 'const hasTracker = Array.isArray(trackerRows) && trackerRows.length > 0;`r`n    const primary = hasTracker ? trackerRows[0] : null;'
$content = $content -replace '\{primary\.current\}/\{primary\.limit \?\? ''âˆž''\}', '\{trackerRows.length\} trackers'
$content = $content -replace '(?s)\{hasTracker \? \(\n\s*<div className="space-y-3">\n\s*<TrackerBar \{\.\.\.trackerRow\} />\n\s*</div>\n\s*\) : \(', '{hasTracker ? (`r`n                                <div className="space-y-3">`r`n                                    {trackerRows.map((row) => {`r`n                                        const Icon = row.icon ?? MessageSquare;`r`n                                        return (`r`n                                            <TrackerBar`r`n                                                key={row.key}`r`n                                                label={row.label}`r`n                                                current={row.current}`r`n                                                limit={row.limit}`r`n                                                percent={row.percent}`r`n                                                icon={Icon}`r`n                                                accent={row.accent ?? ''bg-brand-500''}`r`n                                            />`r`n                                        );`r`n                                    })}`r`n                                </div>`r`n                            ) : ('

$content = $content -replace 'const currentUsage = usePage\(\)\.props\.current_workspace_usage \?\? \{\};\n    const hasTeamLimit = team_members_limit !== null && team_members_limit !== undefined && Number\(team_members_limit\) > 0;\n    const membersLabel = hasTeamLimit \? `\$\{team_members_count\}/\$\{team_members_limit\}` : `\$\{team_members_count\}`;\n    const membersPct = hasTeamLimit \? clampPercent\(\(Number\(team_members_count\) / Number\(team_members_limit\)\) \* 100\) : null;\n    const trackerRow = \{[\s\S]*?\n    \};', @'
const currentUsage = usePage().props.current_workspace_usage ?? {};
    const workspaceTracker = usePage().props.current_workspace_tracker ?? [];
    const hasTeamLimit = team_members_limit !== null && team_members_limit !== undefined && Number(team_members_limit) > 0;
    const membersLabel = hasTeamLimit ? `${team_members_count}/${team_members_limit}` : `${team_members_count}`;
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
    ];'@

$content = $content -replace 'trackerRow=\{trackerRow\}', 'trackerRows={trackerRows}'
Set-Content -Path $path -Value $content -Encoding UTF8
