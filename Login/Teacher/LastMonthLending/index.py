import pandas as pd
import matplotlib.pyplot as plt
import numpy as np
import sys
import json
import japanize_matplotlib
import datetime
from datetime import timedelta
import calendar
from sklearn.neural_network import MLPRegressor


# 月初を求める関数(Y-m-d)
def get_first_date(d):
    return d.replace(day=1)

# 月末を求める関数(Y-m-d)


def get_last_date(d):
    return d.replace(day=calendar.monthrange(d.year, d.month)[1])


# PHPから渡された変数をひとつの文字列として取得
line = sys.stdin.read()
# jsonからオブジェクト型に変換(ここでは辞書型に変換)
data = json.loads(line)

dir = {
    "lent_time": data["lent_time"],
    "lendingCount": np.array(data["count"]).astype(int)
      }


#テスト用データフレーム
# dir2 = {
#     "lent_time": ["2023-07-11", "2023-07-12", "2023-07-13", "2023-07-21"],
#     "lendingCount": np.array(["20", "12", "2", "3"]).astype(int)
# }

df = pd.DataFrame(dir)

# 今日のデータを取得(Y-m-d形式)
today = datetime.date.today()
# 月初を取得
first = get_first_date(today)
# 月末を取得
last = get_last_date(today)

# 1行月末列のnumpy配列生成しを0で初期化
lent_time = np.zeros((1, last.day))
totalLendingCount = np.zeros((1, last.day))


# # 月初から月末までループ(0~30まで31回回る)
for i in range((last - first).days + 1):

    # 月初に一日ずつ足していく（timedeletaは時間の差を表す)
    date = first + timedelta(i)

    # test_dateは貸出があった日かどうかの判定につかう
    test_date = date.strftime("%Y-%m-%d")

    # 日付の追加
    lent_time[0, i] = date.day

    # iが0でないとき一個後ろの要素を取得
    if i != 0:
        totalLendingCount[0, i] = totalLendingCount[0, i - 1]

    # もし貸出日にtest_dataの日付があればループを回す
    if True in df["lent_time"].str.contains(test_date).to_list():

        # df行数分ループ
        for j in df.index:

            # 貸出があった日かどうかの判定
            if df.loc[j, "lent_time"] == test_date:

                # 貸出総数を増加
                totalLendingCount[0, i] = totalLendingCount[0,
                                                            i] + df.loc[j, "lendingCount"]
                break

# 描画領域を準備
fig = plt.figure()
# subplotを設定
ax = fig.add_subplot(111)

# グリッドを設定
ax.grid()
# X軸の範囲を月初から月末に設定
ax.set_xlim(1, last.day)

# x軸y軸のラベルを設定
ax.set_xlabel("貸出日", fontsize=15)
ax.set_ylabel("貸出回数", fontsize=15)

# reshape(1, -1)は行数を2行に設定して列数は自動計算という意味。Tで転置
lent_time = lent_time.reshape(1, -1).T

# 今月における総貸出数の実測値を散布図で表示
ax.scatter(lent_time[0:today.day],
           totalLendingCount[0, 0:today.day], label="今月における総貸出数の実測値")

# ニューラルネットワークで予測
model = MLPRegressor(hidden_layer_sizes=(300, 300, 300, 300),  # 中間層が4層でどの層のノードも300個に指定
                     random_state=1,  # 重みとバイアスの初期化のために生成する乱数のパターンを指定
                     max_iter=900)  # 最大学習回数900回

# 月初から現在までのデータを学習させる
model.fit(lent_time[0:today.day], totalLendingCount[0, 0:today.day])
# 月末までの総貸出数を予測
y_pred = model.predict(lent_time)
# 日付をx軸,総貸出回数をy軸とし、折れ線グラフを描画
ax.plot(lent_time, y_pred, c="black", label="ニューラルネットワークが導いた予測値")
# 月末における貸出回数を予測
predict = model.predict([[last.day]])[0]
titleText = f'今月の貸出状況(今月は{predict:.0f}冊程度貸出があると予測されます)'
# Axesのタイトルを設定
ax.set_title(titleText, c='r', fontsize=18)
plt.legend(fontsize=10)
plt.show()
