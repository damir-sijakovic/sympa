//DAMIR SIJAKOVIC, BSD LICENCE

function ds_paginateSql(itemNumber, perPage, currentPage, visiblePages = 3) {
    // Check if itemNumber is a positive integer; return null if not.
    if (!Number.isInteger(itemNumber) || itemNumber <= 0) {
        return null;
    }

    // Ensure perPage is at least 1 and does not exceed itemNumber.
    perPage = Math.max(1, Math.min(perPage, itemNumber));

    // Calculate the total number of pages.
    const numberOfPages = Math.ceil(itemNumber / perPage);

    // Adjust currentPage to be within the range of available pages.
    currentPage = Math.max(1, Math.min(currentPage, numberOfPages));

    // Calculate the index of the first item on the current page for database queries.
    const limitFirst = (currentPage - 1) * perPage;

    // Determine the number of items to show on the current page.
    const indexRowCount = Math.min(perPage, itemNumber - limitFirst);

    // Calculate the start and end page numbers to be shown in the pagination bar.
    let visibleStartPage = Math.max(1, currentPage - Math.floor(visiblePages / 2));
    let visibleEndPage = Math.min(numberOfPages, visibleStartPage + visiblePages - 1);

    // Adjust the start page if the calculated range is smaller than the desired range of visible pages.
    if (visibleEndPage - visibleStartPage < visiblePages - 1) {
        visibleStartPage = Math.max(1, visibleEndPage - visiblePages + 1);
    }

    // Construct and return an object with the pagination details.
    return {
        itemNumber: itemNumber,
        perPage: perPage,
        currentPage: currentPage,
        numberOfPages: numberOfPages,
        indexOffset: limitFirst, // The offset for database queries.
        indexRowCount: indexRowCount, // Number of rows to retrieve in the query.
        visibleStartPage: visibleStartPage,
        visibleEndPage: visibleEndPage,
        firstPage: 1, // Always 1.
        lastPage: numberOfPages, // The total number of pages.
        prevPage: currentPage > 1 ? currentPage - 1 : null, // Previous page number or null.
        nextPage: currentPage < numberOfPages ? currentPage + 1 : null, // Next page number or null.
    };
}


function ds_paginateArray(itemNumber, perPage, currentPage, visiblePages = 3) {
    // Check if itemNumber is a positive integer; return null if not.
    if (!Number.isInteger(itemNumber) || itemNumber <= 0) {
        return null;
    }

    // Ensure perPage is at least 1 and does not exceed itemNumber.
    perPage = Math.max(1, Math.min(perPage, itemNumber));

    // Calculate the total number of pages.
    const numberOfPages = Math.ceil(itemNumber / perPage);

    // Adjust currentPage to be within the range of available pages.
    currentPage = Math.max(1, Math.min(currentPage, numberOfPages));

    // Calculate the index of the first and last item on the current page for array slicing.
    const sliceStart = (currentPage - 1) * perPage;
    const sliceEnd = Math.min(sliceStart + perPage, itemNumber);

    // Calculate the start and end page numbers to be shown in the pagination bar.
    let visibleStartPage = Math.max(1, currentPage - Math.floor(visiblePages / 2));
    let visibleEndPage = Math.min(numberOfPages, visibleStartPage + visiblePages - 1);

    // Adjust the start page if the calculated range is smaller than the desired range of visible pages.
    if (visibleEndPage - visibleStartPage < visiblePages - 1) {
        visibleStartPage = Math.max(1, visibleEndPage - visiblePages + 1);
    }

    // Construct and return an object with the pagination details.
    return {
        itemNumber: itemNumber,
        perPage: perPage,
        currentPage: currentPage,
        numberOfPages: numberOfPages,
        sliceStart: sliceStart, // The starting index for JavaScript array slicing.
        sliceEnd: sliceEnd, // The ending index for JavaScript array slicing (exclusive).
        visibleStartPage: visibleStartPage,
        visibleEndPage: visibleEndPage,
        firstPage: 1, // Always 1.
        lastPage: numberOfPages, // The total number of pages.
        prevPage: currentPage > 1 ? currentPage - 1 : null, // Previous page number or null.
        nextPage: currentPage < numberOfPages ? currentPage + 1 : null, // Next page number or null.
    };
}


function ds_isSlug(string) {
  return /^[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*$/.test(string);
}
