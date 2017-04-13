page {
    10 < styles.insertContent
    10 =< styles.insertContent
}

styles.insertContent {
    select {
        where = colPos=1
    }
}

# already covered
mod.web_list.alternateBgColors = 1
# Not covered yet
mod {
    web_list {
        alternateBgColors = 1
    }
}

page {
    CLEARGIF {
        value = test
    }

    10 = CLEARGIF
    11 = COLUMNS
    12 = CTABLE
    13 = OTABLE
    14 = HRULER
}
