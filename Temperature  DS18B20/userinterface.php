<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Temperature </title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>


  <br><br><br><br>

  <div class="container-fluid">
    <div class="circle-container">
      <div class="circle" id="circle1">
        <div class="circle-text">TEMP (°C)</div>
        <span class="circle-number" id="temp-value"></span>
      </div>
      
    </div>
  </div>

  <br><br>

  <div class="container mt-3">
    <h2>Attention</h2>
    <p>The following are guides on how to interpret results</p>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Color</th>
          <th>Interpretation</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="bg-success">Green</td>
          <td>Desired range</td>
        </tr>
        <tr>
          <td class="bg-warning">Yellow</td>
          <td>Tolerable</td>
        </tr>
        <tr>
          <td class="bg-danger">Red</td>
          <td>Not tolerable</td>
        </tr>
      </tbody>
    </table>
  </div>

  <br><br><br><br><br><br>


 

  <script>
    
    const parameterNames = {
      'circle1': 'Temp',
    };

    function setBackgroundColor(element, value, ranges) {
      let color;
      let parameterName = parameterNames[element.id];

      if (value >= ranges.best.min && value <= ranges.best.max) {
        color = 'green';
      } else if ((value >= ranges.tolerable.low.min && value <= ranges.tolerable.low.max) ||
                 (value >= ranges.tolerable.high.min && value <= ranges.tolerable.high.max)) {
        color = 'yellow';
       
      } else {
        color = 'red';
        
      }
      element.style.backgroundColor = color;
      
    }

   

    function updateCircleColors() {
      const ranges = {
        temp: {
          best: { min: 23, max: 29 },
          tolerable: { low: { min: 20, max: 22.9 }, high: { min: 29.1, max: 33 } },
          bad: { min: -Infinity, max: 19, high: { min: 33.1, max: Infinity } }
        }
      };

      setBackgroundColor(document.getElementById('circle1'), parseFloat(document.getElementById('temp-value').textContent), ranges.temp);
      
    }

    function fetchData(url, elementId) {
      fetch(url)
        .then(response => response.text())
        .then(data => {
          document.getElementById(elementId).textContent = data;
          updateCircleColors();
        })
        .catch(error => console.error(`Error fetching ${elementId}:`, error));
    }

    document.addEventListener('DOMContentLoaded', () => {
      updateCircleColors();
      setInterval(() => fetchData('fetch_temperature.php', 'temp-value'), 3000);
      
    });
  </script>
</body>
</html>
