function iHoroscopej() {
    let aTmp = [
        [1, 'iWwWj'],
        [19, 'Козерог (22.12–19.1)'],
        [18, 'Водолей (20.1–18.2)'],
        [20, 'Рыбы (19.2–20.3)'],
        [19, 'Овен (21.3–19.4)'],
        [20, 'Телец (20.4–20.5)'],
        [21, 'Близнецы (21.5–21.6)'],
        [22, 'Рак (22.6–22.7)'],
        [22, 'Лев (23.7–22.8)'],
        [22, 'Дева (23.8–22.9)'],
        [22, 'Весы (23.9–22.10)'],
        [22, 'Скорпион (23.10–22.11)'],
        [21, 'Стрелец (23.11–21.12)']
    ];
    let d = document.getElementById('d').value * 1;
    let m = document.getElementById('m').value * 1;
    if (d < 1 || d > 31) {
        m = 0;
        d = 0;
    }
    if (m < 1 || m > 12) {
        m = 0;
        d = 0;
    }
    if (d > aTmp[m][0]) m += 1;
    if (m > 12) m = 1;
    document.getElementById('h').value = aTmp[m][1];
}

export default iHoroscopej;