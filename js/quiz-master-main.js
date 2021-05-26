let qmbuttons = document.getElementsByClassName('qm-square');

for (let i = 0; i < qmbuttons.length; i++) {
    qmbuttons.item(i).addEventListener('click', function(item) {
        item.preventDefault();

        let tbuttons = document.getElementsByClassName('qm-square qm-bactive');
        for (let j = 0; j < tbuttons.length; j++) {
            tbuttons.item(j).classList.remove('qm-bactive');
        }

        let target = item.target;
        target.classList.add('qm-bactive');

        showQuestion(target);
    });
}

function showQuestion(target) {
    let qId = target.id.replace('qm-b', 'q');
    
    let questions = document.getElementsByClassName('qm-qactive');
    for (let z = 0; z < questions.length; z++) {
        let q = questions.item(z);
        q.classList.replace('qm-qactive', 'qm-collapsed');
    }

    let question = document.getElementById(qId);
    question.classList.replace('qm-collapsed', 'qm-qactive');
}

if (qmbuttons.item(0)) {
    qmbuttons.item(0).classList.add('qm-bactive');
    document.getElementsByClassName('qm-collapsed').item(0).classList.replace('qm-collapsed', 'qm-qactive');
}

let bprev = document.getElementById('qm-bprev');
if (bprev) {
    bprev.addEventListener('click', function () {
        let prev = 0;
        for (let x = 0; x < qmbuttons.length; x++) {
            if (qmbuttons.item(x).classList.contains('qm-bactive')) {
                prev = x;
            }
        }
    
        prev--;
        if (prev < 0) {
            prev = 0;
        }
    
        let bp = qmbuttons.item(prev);
        bp.click();
    })
}

let bnext = document.getElementById('qm-bnext');
if (bnext) {
    bnext.addEventListener('click', function () {
        let next = qmbuttons.length - 1;
        for (let x = 0; x < qmbuttons.length; x++) {
            if (qmbuttons.item(x).classList.contains('qm-bactive')) {
                next = x;
            }
        }
    
        next++;
        if (next >= qmbuttons.length) {
            next = qmbuttons.length - 1;
        }
    
        let bn = qmbuttons.item(next);
        bn.click();
    })
}

let autoCommit = false;
function validateQuestionList() {
    if (autoCommit) {
        return true;
    }

    let questionList = document.getElementById('qm-qlist').getElementsByTagName('textarea');
    let answerList = [].map.call(questionList, function(x) {
        return x.value;
    });
    if (answerList.includes('')) {
        if (confirm('Есть пустые ответы! Завершить тест?')) {
            return true;
        }
        else {
            return false;
        }
    }
    return true;
}

let testTimer = document.getElementById('test-timer');    
if (testTimer) {
    setInterval(() => {
        if (testTimer.innerText - 1 > 0) {
            testTimer.innerText = testTimer.innerText - 1;
        } else {
            autoCommit = true;
            document.getElementById('end-button').click();
        }        
    }, 60000);
}

let testForm = document.getElementById('qm-qlist');
if (testForm) {
    let areas = testForm.getElementsByTagName('textarea');
    for (let i = 0; i < areas.length; i++) {
        const area = areas[i];
        area.addEventListener('paste', e => e.preventDefault());
    }
}