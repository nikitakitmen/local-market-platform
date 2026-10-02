/**
 * Простые графики Chart.js. Библиотека загружается отдельным файлом
 * только на страницах, где есть <canvas data-chart>.
 *
 * Пример: <canvas data-chart="line" data-labels='["01.10","02.10"]' data-values='[100,200]' data-label="Выручка, ₽">
 */
import {
    Chart,
    LineController,
    BarController,
    DoughnutController,
    LineElement,
    BarElement,
    ArcElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Filler,
    Tooltip,
    Legend,
} from 'chart.js';

Chart.register(LineController, BarController, DoughnutController, LineElement, BarElement, ArcElement,
    PointElement, CategoryScale, LinearScale, Filler, Tooltip, Legend);

const PRIMARY = '#2f7a55';
const PALETTE = ['#3a7fc1', '#2f7a55', '#8fbfa3', '#c9cfc8'];

Chart.defaults.font.family = "'Manrope Variable', system-ui, sans-serif";
Chart.defaults.color = '#6b7470';

const money = (value) => `${Number(value).toLocaleString('ru-RU')} ₽`;

export function initCharts() {
    document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
        const type = canvas.dataset.chart;
        const labels = JSON.parse(canvas.dataset.labels || '[]');
        const values = JSON.parse(canvas.dataset.values || '[]');
        const isMoney = canvas.dataset.format === 'money';

        if (type === 'doughnut') {
            new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{ data: values, backgroundColor: PALETTE, borderWidth: 0, hoverOffset: 6 }],
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } } },
                },
            });
            return;
        }

        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, canvas.parentElement.clientHeight || 280);
        gradient.addColorStop(0, 'rgba(47, 122, 85, .25)');
        gradient.addColorStop(1, 'rgba(47, 122, 85, 0)');

        new Chart(canvas, {
            type: type === 'bar' ? 'bar' : 'line',
            data: {
                labels,
                datasets: [{
                    label: canvas.dataset.label || '',
                    data: values,
                    borderColor: PRIMARY,
                    backgroundColor: type === 'bar' ? 'rgba(47, 122, 85, .75)' : gradient,
                    borderRadius: type === 'bar' ? 6 : 0,
                    fill: type !== 'bar',
                    tension: .35,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    borderWidth: 2,
                }],
            },
            options: {
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: (item) => (isMoney ? money(item.parsed.y) : item.parsed.y) },
                    },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#eef0ec' },
                        ticks: { callback: (value) => (isMoney ? money(value) : value), maxTicksLimit: 6 },
                    },
                },
            },
        });
    });
}
