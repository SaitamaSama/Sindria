/**
 * Created by ragedwiz on 9/11/16.
 */
class Toast {
    constructor(container) {
        this.container = container;
    }

    show(message) {
        this.container.MaterialSnackbar.showSnackbar({
            message: message
        });
    }
}
