export default {
    setupExtension(app, universe) {
        const menuService = universe.getService('menu');

        // Register header navigation (spec section references in descriptions)
        menuService.registerHeaderMenuItem('CommandIQ', 'console.commandiq', {
            icon: 'satellite-dish',
            priority: 1,
            description: 'Availability forecasting, maintenance scheduling, QC, campaigns, and returns.',
            shortcuts: [
                {
                    title: 'Forecast Board',
                    description: 'LM station-return and MM hub-arrival availability windows.',
                    icon: 'chart-gantt',
                    route: 'console.commandiq.forecast',
                },
                {
                    title: 'Scheduler',
                    description: 'Match work orders to availability windows, technicians, and capacity.',
                    icon: 'calendar-check',
                    route: 'console.commandiq.scheduler',
                },
                {
                    title: 'QC Queue',
                    description: 'Review completed work orders; approve or reject with feedback.',
                    icon: 'clipboard-check',
                    route: 'console.commandiq.qc',
                },
                {
                    title: 'Campaigns',
                    description: 'Retrofit and remediation programs with burn-down tracking.',
                    icon: 'bullhorn',
                    route: 'console.commandiq.campaigns',
                },
                {
                    title: 'Warranty Claims',
                    description: 'Track claims from submission through recovery.',
                    icon: 'file-shield',
                    route: 'console.commandiq.claims',
                },
                {
                    title: 'RMA / Returns',
                    description: 'Manage failed-device returns, dispositions, and core credits.',
                    icon: 'rotate-left',
                    route: 'console.commandiq.rma',
                },
                {
                    title: 'Intake Requests',
                    description: 'Station/DSP device-fault service requests awaiting triage.',
                    icon: 'inbox',
                    route: 'console.commandiq.intake',
                },
            ],
        });
    },
};
