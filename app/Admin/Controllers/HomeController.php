<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Fruit;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin as FacadesAdmin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;

class HomeController extends Controller
{
  public function index(Content $content)
  {
    if (FacadesAdmin::user()->isAdministrator()) {
      return redirect('/admin/fruits');
    } else {
      Admin::script('const buttons = document.querySelectorAll(".button-custom");
            const minValue = 0;
            const maxValue = 10000;
            
            buttons.forEach((button) => {
              button.addEventListener("click", (event) => {
                // 1. Get the clicked element
                const element = event.currentTarget;
                // 2. Get the parent
                const parent = element.parentNode;
                // 3. Get the number (within the parent)
                const numberContainer = parent.querySelector(".number");
                const number = parseFloat(numberContainer.value);
                // 4. Get the minus and plus buttons
                const increment = parent.querySelector(".plus");
                const decrement = parent.querySelector(".minus");
                // 5. Change the number based on click (either plus or minus)
                const newNumber = element.classList.contains("plus")
                  ? number + 1
                  : number - 1;
                numberContainer.value = newNumber;
                // 6. Disable and enable buttons based on number value (and undim number)
                if (newNumber === minValue) {
                  decrement.disabled = true;
                  numberContainer.classList.add("dim");
                  element.blur();
                } else if (newNumber > minValue && newNumber < maxValue) {
                  decrement.disabled = false;
                  increment.disabled = false;
                  numberContainer.classList.remove("dim");
                } else if (newNumber === maxValue) {
                  increment.disabled = true;
                  numberContainer.value = `${newNumber}+`;
                  element.blur();
                }
              });
            });');
      Admin::style(':root {
                --width-container: 540px;
              }
              .container {
                width: var(--width-container);
                max-width: 100%;
                margin: 0 auto;
                padding: 0 var(--space-8);
                border: var(--border);
                background-color: var(--color-white);
                border-radius: var(--radius);
                box-shadow: var(--shadow);
              }
              .input-row {
                // display: flex;
                padding: var(--space-8) 0;
                border-bottom: var(--border);
                margin-bottom:20px;
              }
              .input-row:last-child {
                border-bottom: 0;
              }
              .title {
                text-align:center;
                margin-right: var(--space-8);
              }

              .title img {
                width: 100% ;
              }

              
              @media only screen and (max-width: 991px) {
                .title img{
                  width: 50%;
                }
              }
              
              .label {
                margin-bottom: var(--space-1);
                font-weight: bold;
              }
              .description {
                color: var(--color-gray-600);
              }
              .input {
                display: flex;
                align-items: center;
                margin-left: auto;
                justify-content:center;
              }
              .button-custom {
                display: flex;
                justify-content: center;
                align-items: center;
                width: var(--space-12);
                height: var(--space-12);
                border: 1px solid var(--color-blue-500);
                border-radius: var(--round);
                background-color: var(--color-white);
              }
              .button-custom:hover {
                background-color: var(--color-blue-200);
                cursor: pointer;
              }
              .button-custom:focus {
                outline: none;
                box-shadow: var(--shadow-focus);
              }
              .button-custom[disabled] {
                opacity: var(--opacity-50);
                pointer-events: none;
              }
              .button-custom:active {
                background-color: var(--color-blue-300);
              }
              
              .number {
                font-size: var(--text-lg);
                min-width: var(--space-12);
                text-align: center;
              }
              .icon {
                user-select: none;
              }
              .dim {
                color: var(--color-gray-400);
              }
              
              ');
      return $content
        ->title('Dashboard')
        ->row(function (Row $row) {
          $fruits = Fruit::where('status', 1)->get();
          foreach ($fruits as $data) {
            $row->column(2, function (Column $column) use ($data) {
              $column->append('<div class="container">
                        <div class="input-row">
                          <div class="title">
                            <img src="' . $data->image . '" height="150">
                            <h4>' . $data->name . '</h4>
                            
                          </div>
                          <div class="input">
                            <button class="button-custom minus" aria-label="Decrease by one" disabled>
                              <svg width="16" height="2" viewBox="0 0 16 2" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <line y1="1" x2="16" y2="1" stroke="#0064FE" stroke-width="2" class="icon" />
                              </svg>
                            </button>
                            <input type="number" class="number dim" id="' . $data->id . '" min="0" value="0" style="width:5em">
                            <button class="button-custom plus" aria-label="Increase by one">
                              <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon">
                                <line x1="8" y1="4.37114e-08" x2="8" y2="16" stroke="#0064FE" stroke-width="2" />
                                <line y1="8" x2="16" y2="8" stroke="#0064FE" stroke-width="2" />
                              </svg>
                      
                            </button>
                          </div>
                        </div>
                      </div>');
            });
          }
          $row->column(12, function (Column $column) {
            $column->append('<div style="margin-top:20px;" class="text-center"><button class="btn btn-success" onclick="submitStock()">提交</button></div>');
          });
          Admin::html('<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>async function submitStock(){
                    const numbers = document.querySelectorAll(".number");
                    var fruitarray = [];
                    numbers.forEach((div) => {
                        fruitarray.push({
                            id: div.id,
                            number: div.value
                        });
                    });

                    const inputOptions = new Promise((resolve) => {
                      setTimeout(() => {
                        resolve({
                          "1": "售價",
                          "2": "員工價",
                        })
                      }, 1000)
                    })
                    
                    const { value: selectoption } = await swal.fire({
                      type: "question",
                      title: "請選擇此操作類型",
                      input: "radio",
                      inputOptions: inputOptions,
                      inputValidator: (value) => {
                        if (!value) {
                          return "請選擇其中一個!"
                        }
                      }
                    })
                    
                    if (selectoption) {
                      swal.fire({
                        title: "確定執行？",
                        text: "請確認資料無誤",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "確定",
                        cancelButtonText: "取消"
                      }).then((result) => {
                        if (result.value) {
                          $.ajaxSetup({
                            headers: {
                                "X-CSRF-TOKEN": $("meta[name=' . "csrf-token" . ']").attr("content")
                            }
                        });
                        $.ajax({
                            type: "POST",
                            url: "/admin/takefruit",
                            data: {data: fruitarray,agent:' . FacadesAdmin::user()->id . ',type:selectoption},
                            dataType: "json",
                            success: function (data) {
                              console.log(data);

                              Swal.fire(
                                "成功!",
                                "此動作已被記錄.",
                                "success"
                              );
                              numbers.forEach((div) => {
                                div.value = 0;
                            });
                            },
                            error: function (data) {
                              Swal.fire(
                                "出錯!",
                                "請嘗試刷新頁面.",
                                "error"
                              );
                                console.log(data);
                            }
                        });
                        }
                      })
                    }
                }</script>');
        });
    }
  }
}
