<script setup>
import Chart from 'chart.js/auto';
import { onMounted } from 'vue';

const data = {
  labels: [
    'Spam',
    'Rude',
    'Abuse',
    'Posts'
  ],
  datasets: [{
    data: [300, 50, 100, 200],
    backgroundColor: [
      'rgb(255, 99, 132)',
      'rgb(54, 162, 235)',
      'rgb(255, 205, 86)',
      'orange'
    ],
    hoverOffset: 4
  }]
};

const config = {
  type: 'pie',
  data: data,
  options: {
    responsive: true
  }

};

  onMounted(() => {
    const pieChart = new Chart(
    document.getElementById('pieChart'),
    config
  );
  })
</script>


<template>
    <div>
        <canvas id="pieChart"></canvas>
    </div>
</template>
