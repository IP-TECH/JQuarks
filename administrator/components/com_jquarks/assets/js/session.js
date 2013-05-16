
function onLoadWindow(doc)
{
    // allow to group multiple choices questions statement into one unique cell
    questionRows = doc.getElementById('results').getElementsByTagName('tbody')[0].getElementsByTagName('tr') ;

    currentQuest = 0 ;
    nextQuest = 1 ;

    while (currentQuest < questionRows.length )
    {
        span = 1 ;

        while ( nextQuest < questionRows.length && questionRows[currentQuest].getElementsByTagName('td')[0].innerHTML == questionRows[nextQuest].getElementsByTagName('td')[0].innerHTML )
        {
                span++ ;
                nextQuest++ ;
        }

        for (i = currentQuest + 1 ; i < nextQuest ; i++) {
                questionRows[i].deleteCell(0) ;
        }

        questionRows[currentQuest].getElementsByTagName('td')[0].rowSpan=span

        currentQuest = nextQuest ;
        nextQuest++ ;
    }
}

function submitAnswer(sessAnsId, type)
{
    document.adminForm.sessAnsId.value = sessAnsId ;
    document.adminForm.task.value = type ;
    document.adminForm.submit() ;
}
