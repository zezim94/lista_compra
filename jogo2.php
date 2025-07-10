<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jogo de Multiplicação</title>
  <style>
    body { font-family: 'Arial', sans-serif; text-align: center; background-color: #f0f8ff; padding: 20px; }
    button { padding: 10px 20px; font-size: 16px; margin: 10px; cursor: pointer; }
    .question { font-size: 32px; color: #333; margin: 20px; }
  </style>
</head>
<body>

  <h1>Jogo de Multiplicação</h1>
  <div class="question" id="question">Pergunta aqui</div>
  <input type="number" id="answer" placeholder="Sua resposta" />
  <button onclick="checkAnswer()">Responder</button>
  <p id="feedback"></p>
  <button onclick="nextQuestion()" style="display:none;">Próxima Pergunta</button>
  <p>Tempo: <span id="timer">30</span> segundos</p>

  <script>
    let time = 30;
    let score = 0;
    let correctAnswer;

    function startTimer() {
      const timer = setInterval(() => {
        if (time > 0) {
          time--;
          document.getElementById('timer').textContent = time;
        } else {
          clearInterval(timer);
          alert('Tempo acabou!');
        }
      }, 1000);
    }

    function generateQuestion() {
      const num1 = Math.floor(Math.random() * 10) + 1;
      const num2 = Math.floor(Math.random() * 10) + 1;
      correctAnswer = num1 * num2;

      document.getElementById('question').textContent = `${num1} x ${num2}`;
    }

    function checkAnswer() {
      const userAnswer = parseInt(document.getElementById('answer').value);
      if (userAnswer === correctAnswer) {
        document.getElementById('feedback').textContent = 'Correto!';
        score++;
      } else {
        document.getElementById('feedback').textContent = 'Errado, tente novamente!';
      }
      document.getElementById('next').style.display = 'block';
    }

    function nextQuestion() {
      document.getElementById('next').style.display = 'none';
      generateQuestion();
    }

    generateQuestion();
    startTimer();
  </script>

</body>
</html>
