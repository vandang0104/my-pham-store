// Biểu đồ đường cho trang thống kê admin (Chart.js, chỉ nạp khi trang có <canvas data-chart>).
import { CategoryScale, Chart, Filler, LinearScale, LineController, LineElement, PointElement, Tooltip } from 'chart.js';

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Filler);

const vnd = new Intl.NumberFormat('vi-VN');

export function initCharts(canvases) {
    canvases.forEach((canvas) => {
        const { labels, data, label } = JSON.parse(canvas.dataset.chart);

        new Chart(canvas, {
            type: 'line',
            data: { labels, datasets: [{ label, data, borderColor: '#2E5A4C', backgroundColor: 'rgba(46, 90, 76, 0.12)', fill: true, tension: 0.25 }] },
            options: {
                plugins: { tooltip: { callbacks: { label: (ctx) => `${vnd.format(ctx.parsed.y)} ₫` } } },
                scales: { y: { beginAtZero: true, ticks: { callback: (v) => vnd.format(v) } } },
            },
        });
    });
}
