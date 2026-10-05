import "bootstrap";
import Chart from "chart.js/auto";

const report = document.getElementById("report-chart");
const reportSpec = document.getElementById("report-chart-data");

if (report && reportSpec) {
    new Chart(report, JSON.parse(reportSpec.textContent));
}

const chat = document.querySelector("[data-chat]");

if (chat) {
    let after = Number(chat.dataset.after || 0);

    const pull = async () => {
        const response = await fetch(`${chat.dataset.chat}?after=${after}`, {
            headers: { Accept: "application/json" },
        });

        if (!response.ok) {
            return;
        }

        const payload = await response.json();

        payload.messages.forEach((message) => {
            const line = document.createElement("p");
            const strong = document.createElement("strong");

            line.className = "mb-1";
            strong.textContent = `${message.name}: `;
            line.append(strong, document.createTextNode(message.body));
            chat.append(line);
            after = message.id;
        });
    };

    window.setInterval(pull, 4000);
}

document.querySelectorAll("[data-color-field]").forEach((input) => {
    const label = document.querySelector(`[data-color-value="${input.dataset.colorField}"]`);

    input.addEventListener("input", () => {
        if (label) {
            label.textContent = input.value;
        }

                document.documentElement.style.setProperty("--app-accent", input.value);
    });
});

const orgBoard = document.querySelector("[data-org-board]");

if (orgBoard) {
    const tree = orgBoard.querySelector("[data-org-tree]");
    let scale = 1;
    let startDistance = 0;
    let startScale = 1;

    const applyScale = () => {
        tree.style.transform = `scale(${scale})`;
    };

    document.querySelector("[data-org-zoom-in]")?.addEventListener("click", () => {
        scale = Math.min(2, Math.round((scale + 0.1) * 10) / 10);
        applyScale();
    });

    document.querySelector("[data-org-zoom-out]")?.addEventListener("click", () => {
        scale = Math.max(0.4, Math.round((scale - 0.1) * 10) / 10);
        applyScale();
    });

    const distance = (touches) => {
        const [first, second] = touches;

        return Math.hypot(first.clientX - second.clientX, first.clientY - second.clientY);
    };

    orgBoard.addEventListener("touchstart", (event) => {
        if (event.touches.length !== 2) {
            return;
        }

        startDistance = distance(event.touches);
        startScale = scale;
    }, { passive: true });

    orgBoard.addEventListener("touchmove", (event) => {
        if (event.touches.length !== 2 || startDistance === 0) {
            return;
        }

        event.preventDefault();
        scale = Math.min(2, Math.max(0.4, startScale * (distance(event.touches) / startDistance)));
        applyScale();
    }, { passive: false });
}

const builder = document.querySelector("[data-form-builder]");

if (builder) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const errorBox = document.querySelector("[data-builder-error]");

    const showError = (message) => {
        if (!errorBox) {
            return;
        }

        errorBox.hidden = false;
        errorBox.textContent = message;
    };

    const send = async (url, payload) => {
        const response = await fetch(url, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": token,
            },
            body: JSON.stringify(payload),
        });

        if (!response.ok) {
            const data = await response.json();
            const message = Object.values(data.errors ?? {}).flat()[0] ?? "Kaydedilemedi.";
            showError(message);

            return null;
        }

        if (errorBox) {
            errorBox.hidden = true;
        }

        return response.json();
    };

    const preview = (selector, value) => {
        const node = builder.querySelector(selector);

        if (node) {
            node.textContent = value;
        }
    };

    builder.querySelector("[data-form-meta]")?.addEventListener("focusout", (event) => {
        if (!event.target.name) {
            return;
        }

        const form = event.currentTarget;
        const body = Object.fromEntries(new FormData(form).entries());
        delete body._method;
        delete body._token;
        send(form.action, body);
        preview("[data-preview='title']", body.name ?? "");
        preview("[data-preview='description']", body.description ?? "");
    });

    builder.querySelectorAll("[data-save='prompt']").forEach((input) => {
        input.addEventListener("input", () => {
            const card = input.closest("[data-question-id]");
            preview(`[data-preview-prompt="${card.dataset.questionId}"]`, input.value);
        });
    });

    builder.querySelectorAll("[data-option-url]").forEach((input) => {
        input.addEventListener("input", () => {
            const row = input.closest("[data-option-id]");
            preview(`[data-preview-option="${row.dataset.optionId}"]`, input.value);
        });
        let timer = null;
        const queue = () => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                if (builder.hasAttribute("data-draft")) {
                    send(input.dataset.optionUrl, { label: input.value });
                }
            }, 400);
        };
        input.addEventListener("input", queue);
        input.addEventListener("focusout", () => {
            clearTimeout(timer);
            if (builder.hasAttribute("data-draft")) {
                send(input.dataset.optionUrl, { label: input.value });
            }
        });
    });

    builder.querySelectorAll("[data-row-url]").forEach((input) => {
        input.addEventListener("input", () => {
            const row = input.closest(".form-option");
            const id = input.dataset.rowUrl.split("/").pop();
            preview(`[data-preview-row="${id}"]`, input.value);
        });
        let timer = null;
        const queue = () => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                if (builder.hasAttribute("data-draft")) {
                    send(input.dataset.rowUrl, { label: input.value });
                }
            }, 400);
        };
        input.addEventListener("input", queue);
        input.addEventListener("focusout", () => {
            clearTimeout(timer);
            if (builder.hasAttribute("data-draft")) {
                send(input.dataset.rowUrl, { label: input.value });
            }
        });
    });

    builder.querySelectorAll("[data-section-url]").forEach((input) => {
        input.addEventListener("input", () => {
            const section = input.closest(".form-section");
            const id = input.dataset.sectionUrl.split("/").at(-1);
            preview(`[data-preview-section="${id}"]`, input.value);
        });
        let timer = null;
        const queue = () => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                if (builder.hasAttribute("data-draft")) {
                    send(input.dataset.sectionUrl, { title: input.value });
                }
            }, 400);
        };
        input.addEventListener("input", queue);
        input.addEventListener("focusout", () => {
            clearTimeout(timer);
            if (builder.hasAttribute("data-draft")) {
                send(input.dataset.sectionUrl, { title: input.value });
            }
        });
    });

    let saveTicket = 0;

    builder.querySelectorAll(".form-q").forEach((card) => {
        const save = async () => {
            if (!builder.hasAttribute("data-draft")) {
                return;
            }

            const ticket = ++saveTicket;
            const payload = {};

            card.querySelectorAll("[data-save]").forEach((field) => {
                payload[field.dataset.save] = field.type === "checkbox" ? field.checked : field.value;
            });

            const result = await send(card.dataset.saveUrl, payload);

            if (ticket !== saveTicket) {
                return;
            }

            if (!result) {
                const select = card.querySelector("[data-save='type']");

                if (select) {
                    select.value = card.dataset.type;
                }

                return;
            }

            if (result.reload) {
                window.location.reload();
            }
        };

        card.querySelectorAll("[data-save]").forEach((field) => {
            const immediate = field.tagName === "SELECT" || field.type === "checkbox" || field.type === "number";
            let timer = null;

            const queue = () => {
                clearTimeout(timer);
                timer = setTimeout(save, 400);
            };

            if (immediate) {
                field.addEventListener("change", save);
            } else {
                field.addEventListener("input", queue);
                field.addEventListener("focusout", () => {
                    clearTimeout(timer);
                    save();
                });
            }
        });
    });

    const focused = builder.querySelector("[data-focus] [data-save='prompt']");
    focused?.focus();
    focused?.select();

    let dragged = null;

    builder.querySelectorAll("[data-drag-handle]").forEach((handle) => {
        const item = handle.closest("[data-sort-item]");

        handle.addEventListener("pointerdown", () => {
            item.draggable = true;
        });

        item.addEventListener("dragstart", (event) => {
            event.stopPropagation();
            dragged = item;
            item.classList.add("is-drag");
            event.dataTransfer.effectAllowed = "move";
        });

        item.addEventListener("dragend", (event) => {
            event.stopPropagation();
            item.draggable = false;
            item.classList.remove("is-drag");
            const kind = item.dataset.kind;

            builder.querySelectorAll(`[data-sort-list][data-kind="${kind}"]`).forEach((list) => {
                const key = kind === "option" ? "option_ids" : "question_ids";
                const ids = [...list.querySelectorAll(`:scope > [data-kind="${kind}"]`)].map((node) => Number(node.dataset.questionId || node.dataset.optionId));

                fetch(list.dataset.sortUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": token,
                    },
                    body: JSON.stringify({ [key]: ids }),
                });
            });

            dragged = null;
        });
    });

    builder.querySelectorAll("[data-sort-list]").forEach((list) => {
        list.addEventListener("dragover", (event) => {
            if (!dragged || dragged.dataset.kind !== list.dataset.kind) {
                return;
            }

            event.preventDefault();
            const target = event.target.closest("[data-sort-item]");

            if (!target || target === dragged || target.dataset.kind !== dragged.dataset.kind) {
                list.appendChild(dragged);

                return;
            }

            const bounds = target.getBoundingClientRect();
            const after = event.clientY > bounds.top + bounds.height / 2;
            list.insertBefore(dragged, after ? target.nextSibling : target);
        });
    });
}
